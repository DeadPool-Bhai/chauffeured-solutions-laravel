<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeadEmails;
use App\Models\Lead;
use App\Services\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Handles the forms defined in config/forms.php (wedding quote, wedding booking). */
class FormLeadController extends Controller
{
    public function submit(Request $request, Recaptcha $recaptcha, string $form)
    {
        $config = config('forms.'.$form) ?? abort(404);

        $validator = Validator::make($request->all(), $this->rules($config), [], $this->labels($config));
        $validator->validate();

        if (! $recaptcha->passes($request->input('g-recaptcha-response'), $request->ip())) {
            return back()->withInput()->withErrors(['captcha' => 'We could not verify your request. Please try again, or call us on '.config('site.phone').'.']);
        }

        $names = collect($config['fields'])->pluck('name')->filter()->reject(fn ($n) => $n === 'terms')->all();
        $payload = collect($request->only($names))->map(fn ($v) => is_string($v) ? trim($v) : $v)->filter(fn ($v) => $v !== null && $v !== '')->all();

        $lead = Lead::create([
            'form_key' => $config['form_key'],
            'payload' => $payload,
            'source_page_title' => $request->input('page_title'),
            'source_path' => $request->input('source_path'),
            'referer' => substr((string) $request->headers->get('referer'), 0, 500) ?: null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        SendLeadEmails::dispatch($lead->id);

        if (($config['redirect'] ?? 'thank_you') === 'back') {
            return back()->with('form_sent_'.$form, $config['success']);
        }

        return redirect()->away($request->getSchemeAndHttpHost().config('leads.thank_you_path'));
    }

    private function rules(array $config): array
    {
        $rules = [
            'website' => ['nullable', 'max:0'], // honeypot
            'page_title' => ['nullable', 'string', 'max:255'],
            'source_path' => ['nullable', 'string', 'max:255'],
            'g-recaptcha-response' => ['nullable', 'string'],
        ];

        foreach ($config['fields'] as $f) {
            if (! isset($f['name'])) {
                continue;
            }
            $r = [empty($f['required']) ? 'nullable' : 'required'];
            $r = array_merge($r, match ($f['type']) {
                'email' => ['email:rfc', 'max:'.($f['max'] ?? 190)],
                'tel' => ['string', 'min:6', 'max:'.($f['max'] ?? 40), 'regex:/^[0-9+()\-\s.]+$/'],
                'date' => ['date'],
                'time' => ['date_format:H:i'],
                'number' => ['integer', 'min:'.($f['min'] ?? 0), 'max:'.($f['max'] ?? 99)],
                'checkbox' => ['accepted'],
                default => ['string', 'max:'.($f['max'] ?? 255)],
            });
            $rules[$f['name']] = $r;
        }

        return $rules;
    }

    private function labels(array $config): array
    {
        return collect($config['fields'])->filter(fn ($f) => isset($f['name']))->mapWithKeys(fn ($f) => [$f['name'] => strtolower($f['label'])])->all();
    }
}
