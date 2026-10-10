@php
    use App\Support\Shortcodes;
    use App\Support\SectionSplitter;
    $body = Shortcodes::expand($page->body_html ?? '');
    $posted = $page->published_at;
@endphp
<x-layout :page="$page">
    <article class="mx-auto max-w-3xl px-5 py-16 md:py-24">
        <p class="text-sm font-medium uppercase tracking-wide text-gold"><a href="/blog/">Blog</a></p>
        <h1 class="mt-3 font-display text-4xl font-semibold leading-tight md:text-5xl">{{ $page->h1 }}</h1>
        @if ($posted)
            <p class="mt-4 text-sm text-white/60"><time datetime="{{ $posted->toDateString() }}">{{ $posted->format('j F Y') }}</time></p>
        @endif
        @if ($page->featured_image && ! str_contains($page->body_html ?? '', $page->featured_image))
            <img src="{{ $page->featured_image }}" alt="{{ $page->h1 }}" class="mt-8 w-full rounded-2xl" fetchpriority="high">
        @endif
        <div class="prose-cs mt-10">{!! $body !!}</div>

        <aside class="mt-14 rounded-3xl bg-gold/10 p-8">
            <p class="font-heading text-2xl font-semibold">Need a chauffeur in Sydney?</p>
            <p class="mt-2 text-white/80">Book online in minutes or ask for a free quote.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="/booking/" class="btn btn-gold">Quick Booking</a>
                <button type="button" @click="quote = true" class="btn btn-ghost">Get a Free Quote</button>
            </div>
        </aside>
    </article>
</x-layout>
