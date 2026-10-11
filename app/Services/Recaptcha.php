<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha
{
    public function enabled(): bool
    {
        return filled(config('leads.recaptcha.secret'));
    }

    /** reCAPTCHA v3. When no secret is configured (local dev) verification is skipped. */
    public function passes(?string $token, ?string $ip = null, string $action = 'quote'): bool
    {
        if (! $this->enabled()) {
            return true;
        }
        if (blank($token)) {
            return false;
        }

        try {
            $res = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('leads.recaptcha.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ])->json();
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA request failed: '.$e->getMessage());

            return false;
        }

        return ($res['success'] ?? false)
            && ($res['action'] ?? $action) === $action
            && ($res['score'] ?? 0) >= config('leads.recaptcha.min_score');
    }
}
