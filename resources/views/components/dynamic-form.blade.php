@props(['form'])
@php
    $config = config('forms.'.$form);
    $siteKey = config('leads.recaptcha.site_key');
    $sent = session('form_sent_'.$form);
    $input = 'mt-1 w-full rounded-xl border border-white/20 bg-ink px-4 py-2.5 text-sm text-white placeholder-white/40 focus:border-gold focus:outline-none focus:ring-1 focus:ring-gold [color-scheme:dark]';
    $span = ['half' => 'sm:col-span-3', 'third' => 'sm:col-span-2'];
    $type = fn ($t) => $t;
@endphp
@if ($sent)
    <div class="rounded-2xl border border-gold/40 bg-gold/10 p-6 text-sm" role="status">{{ $sent }}</div>
@else
<form method="POST" action="{{ route('lead.form', $form) }}" x-data="quoteForm(@js($siteKey))" @submit="submit($event)" class="space-y-4" novalidate>
    @csrf
    <input type="hidden" name="page_title" x-ref="title">
    <input type="hidden" name="source_path" x-ref="path">
    <input type="hidden" name="g-recaptcha-response" x-ref="token">
    <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">
            {{ $errors->has('captcha') ? $errors->first('captcha') : 'Please check the highlighted fields and try again.' }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-6">
        @foreach ($config['fields'] as $f)
            @if (isset($f['group']))
                <p class="border-b border-white/10 pb-2 pt-4 font-heading text-lg font-semibold text-gold sm:col-span-6">{{ $f['group'] }}</p>
                @continue
            @endif
            @php
                $n = $f['name'];
                $id = "df-{$form}-{$n}";
                $req = ! empty($f['required']);
                $col = $span[$f['width'] ?? ''] ?? 'sm:col-span-6';
            @endphp
            <div class="{{ $col }}">
                @if ($f['type'] === 'checkbox')
                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" name="{{ $n }}" value="1" @checked(old($n)) class="mt-1 size-4 accent-[#c9a227]">
                        <span>{{ $f['label'] }} @if ($req)<span class="text-gold">*</span>@endif</span>
                    </label>
                @else
                    <label for="{{ $id }}" class="text-sm font-medium">{{ $f['label'] }} @if ($req)<span class="text-gold">*</span>@endif</label>
                    @if ($f['type'] === 'textarea')
                        <textarea id="{{ $id }}" name="{{ $n }}" rows="3" @required($req) class="{{ $input }}">{{ old($n) }}</textarea>
                    @else
                        <input id="{{ $id }}" name="{{ $n }}" type="{{ $f['type'] }}" value="{{ old($n) }}" @required($req)
                            @isset($f['min']) min="{{ $f['min'] }}" @endisset @isset($f['max']) @if ($f['type'] === 'number') max="{{ $f['max'] }}" @endif @endisset
                            class="{{ $input }}">
                    @endif
                @endif
                @error($n)<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-gold w-full sm:w-auto" :disabled="busy" x-text="busy ? 'Sending…' : @js($config['submit'])">{{ $config['submit'] }}</button>

    @if ($siteKey)
        <p class="text-xs text-white/50">Protected by reCAPTCHA. Google's <a href="https://policies.google.com/privacy" rel="noopener" class="underline">Privacy Policy</a> and <a href="https://policies.google.com/terms" rel="noopener" class="underline">Terms of Service</a> apply.</p>
    @endif
</form>
@endif
