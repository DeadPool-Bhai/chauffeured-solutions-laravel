@props(['ctx' => 'page'])
@php
    $active = old('_ctx', $ctx) === $ctx;
    $val = fn ($k) => $active ? old($k) : null;
    $err = fn ($k) => $active && $errors->has($k) ? $errors->first($k) : null;
    $siteKey = config('leads.recaptcha.site_key');
    $input = 'mt-1 w-full rounded-xl border border-white/20 bg-ink px-4 py-2.5 text-sm text-white placeholder-white/40 focus:border-gold focus:outline-none focus:ring-1 focus:ring-gold';
    $id = fn ($k) => "qf-{$ctx}-{$k}";
@endphp
<form method="POST" action="{{ route('lead.quote') }}" x-data="quoteForm(@js($siteKey))" @submit="submit($event)" class="space-y-4" novalidate>
    @csrf
    <input type="hidden" name="_ctx" value="{{ $ctx }}">
    <input type="hidden" name="page_title" x-ref="title">
    <input type="hidden" name="source_path" x-ref="path">
    <input type="hidden" name="g-recaptcha-response" x-ref="token">
    <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    @if ($active && $errors->any())
        <div class="rounded-xl border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">
            @if ($errors->has('captcha'))
                {{ $errors->first('captcha') }}
            @else
                Please check the highlighted fields and try again.
            @endif
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $id('name') }}" class="text-sm font-medium">Name <span class="text-gold">*</span></label>
            <input id="{{ $id('name') }}" name="name" type="text" required autocomplete="name" value="{{ $val('name') }}" placeholder="Name" class="{{ $input }}">
            @if ($e = $err('name'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
        <div>
            <label for="{{ $id('email') }}" class="text-sm font-medium">Email Address <span class="text-gold">*</span></label>
            <input id="{{ $id('email') }}" name="email" type="email" required autocomplete="email" value="{{ $val('email') }}" placeholder="Email Address" class="{{ $input }}">
            @if ($e = $err('email'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
        <div>
            <label for="{{ $id('phone') }}" class="text-sm font-medium">Contact Number <span class="text-gold">*</span></label>
            <input id="{{ $id('phone') }}" name="phone" type="tel" required autocomplete="tel" value="{{ $val('phone') }}" placeholder="Contact Number" class="{{ $input }}">
            @if ($e = $err('phone'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
        <div>
            <label for="{{ $id('pickup_at') }}" class="text-sm font-medium">Pick-up Date &amp; Time <span class="text-gold">*</span></label>
            <input id="{{ $id('pickup_at') }}" name="pickup_at" type="datetime-local" required value="{{ $val('pickup_at') }}" class="{{ $input }} [color-scheme:dark]">
            @if ($e = $err('pickup_at'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
    </div>

    <div>
        <label for="{{ $id('flight_info') }}" class="text-sm font-medium">Additional info</label>
        <input id="{{ $id('flight_info') }}" name="flight_info" type="text" value="{{ $val('flight_info') }}" placeholder="Additional info: Flight number/Airport Name etc." class="{{ $input }}">
        @if ($e = $err('flight_info'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
    </div>

    <div>
        <label for="{{ $id('pickup_address') }}" class="text-sm font-medium">Pick Up Address <span class="text-gold">*</span></label>
        <input id="{{ $id('pickup_address') }}" name="pickup_address" type="text" required autocomplete="street-address" value="{{ $val('pickup_address') }}" placeholder="Pick Up Address" class="{{ $input }}">
        @if ($e = $err('pickup_address'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            <input name="pickup_city" type="text" aria-label="City" autocomplete="address-level2" value="{{ $val('pickup_city') }}" placeholder="City" class="{{ $input }}">
            <input name="pickup_state" type="text" aria-label="State" autocomplete="address-level1" value="{{ $val('pickup_state') }}" placeholder="State" class="{{ $input }}">
            <input name="pickup_zip" type="text" aria-label="Post Code" autocomplete="postal-code" value="{{ $val('pickup_zip') }}" placeholder="Post Code" class="{{ $input }}">
        </div>
    </div>

    <div>
        <label for="{{ $id('dropoff_address') }}" class="text-sm font-medium">Drop off Address <span class="text-gold">*</span></label>
        <input id="{{ $id('dropoff_address') }}" name="dropoff_address" type="text" required value="{{ $val('dropoff_address') }}" placeholder="Drop off Address" class="{{ $input }}">
        @if ($e = $err('dropoff_address'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $id('passengers') }}" class="text-sm font-medium">No. of passengers <span class="text-gold">*</span></label>
            <input id="{{ $id('passengers') }}" name="passengers" type="number" min="1" max="99" required value="{{ $val('passengers') }}" class="{{ $input }}">
            @if ($e = $err('passengers'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
        <div>
            <label for="{{ $id('luggage') }}" class="text-sm font-medium">Luggage count <span class="text-gold">*</span></label>
            <input id="{{ $id('luggage') }}" name="luggage" type="number" min="0" max="99" required value="{{ $val('luggage') }}" class="{{ $input }}">
            @if ($e = $err('luggage'))<p class="mt-1 text-xs text-red-300">{{ $e }}</p>@endif
        </div>
    </div>

    <button type="submit" class="btn btn-gold w-full sm:w-auto" :disabled="busy" x-text="busy ? 'Sending…' : 'Submit for Quote'">Submit for Quote</button>

    @if ($siteKey)
        <p class="text-xs text-white/50">Protected by reCAPTCHA. Google's <a href="https://policies.google.com/privacy" rel="noopener" class="underline">Privacy Policy</a> and <a href="https://policies.google.com/terms" rel="noopener" class="underline">Terms of Service</a> apply.</p>
    @endif
</form>
