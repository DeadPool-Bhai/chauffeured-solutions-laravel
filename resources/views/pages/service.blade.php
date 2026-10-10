@php
    use App\Support\SectionSplitter;
    use App\Support\Shortcodes;
    $related = [];
    $bodyHtml = preg_replace_callback('#<h2>\s*Related posts\s*</h2>((?:\s*<h2>\s*<a [^>]*>.*?</a>\s*</h2>)+)#is', function ($m) use (&$related) {
        preg_match_all('#<a href="([^"]+)"[^>]*>(.*?)</a>#is', $m[1], $l, PREG_SET_ORDER);
        foreach ($l as $x) { $related[] = ['href' => $x[1], 'title' => trim(html_entity_decode(strip_tags($x[2]), ENT_QUOTES))]; }
        return '';
    }, $page->body_html ?? '');
    $parts = SectionSplitter::byH2($bodyHtml);
    // Hero lead: first paragraph of the intro. The Quick Booking link is rendered as a button.
    preg_match('#<p>(?!<a)(.*?)</p>#is', $parts['intro'], $lead);
    $badges = ['Best chauffeur service for large events & conferences', 'Over 40 years experience', 'Professional & courteous chauffeurs'];
    $kind = fn ($t) => match (true) {
        (bool) preg_match('/quote/i', $t) => 'quote',
        (bool) preg_match('/testimonial/i', $t) => 'reviews',
        (bool) preg_match('/^FAQ/i', $t) => 'faq',
        (bool) preg_match('/^Ready to/i', $t) => 'cta',
        default => 'prose',
    };
@endphp
<x-layout :page="$page">
    <section class="relative isolate flex min-h-[420px] items-center overflow-hidden">
        <img src="/wp-content/uploads/2025/05/chauffeured-solutions-Australia.jpg" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-black/65"></div>
        <div class="mx-auto w-full max-w-7xl px-5 py-16">
            <h1 class="font-display text-4xl font-semibold leading-tight md:text-5xl">{{ $page->h1 }}</h1>
            @if (! empty($lead[1]))
                <p class="mt-5 max-w-3xl text-white/85">{!! $lead[1] !!}</p>
            @endif
            <a href="/booking/" class="btn btn-gold mt-8">Quick Booking</a>
        </div>
    </section>

    @foreach ($parts['sections'] as $s)
        @switch($kind($s['title']))
            @case('quote')
                <section class="bg-ink" id="quote">
                    <div class="mx-auto max-w-3xl px-5 py-16">
                        <h2 class="font-heading text-3xl font-semibold">{{ $s['title'] }}</h2>
                        <div class="mt-8"><x-quote-form ctx="page" /></div>
                    </div>
                </section>
                <ul class="mx-auto grid max-w-7xl gap-4 px-5 py-8 text-sm font-medium sm:grid-cols-3">
                    @foreach ($badges as $b)
                        <li class="flex items-center gap-3 rounded-2xl bg-ink-2 px-5 py-4"><span class="text-gold" aria-hidden="true">&#10003;</span>{{ $b }}</li>
                    @endforeach
                </ul>
                @break

            @case('reviews')
                <section class="mx-auto max-w-7xl px-5 pt-16">
                    <h2 class="font-heading text-3xl font-semibold md:text-4xl">{{ $s['title'] }}</h2>
                </section>
                <x-reviews />
                @break

            @case('faq')
                <section class="mx-auto max-w-4xl px-5 py-16">
                    <h2 class="mb-8 font-heading text-3xl font-semibold md:text-4xl">{{ $s['title'] }}</h2>
                    <x-faq :items="SectionSplitter::byH3($s['html'])['items']" />
                </section>
                @break

            @case('cta')
                <section class="bg-gold/10">
                    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-14 md:flex-row md:items-center md:justify-between">
                        <h2 class="max-w-2xl font-heading text-2xl font-semibold md:text-3xl">{{ $s['title'] }}</h2>
                        <div class="flex gap-3">
                            <a href="/booking/" class="btn btn-gold">Quick Booking</a>
                            <button type="button" @click="quote = true" class="btn btn-ghost">Get a Free Quote</button>
                        </div>
                    </div>
                </section>
                @break

            @default
                <section class="mx-auto max-w-4xl px-5 py-12">
                    <h2 class="font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $s['title'] }}</h2>
                    <div class="prose-cs prose-cs--cards mt-6">{!! Shortcodes::strip($s['html']) !!}</div>
                </section>
        @endswitch
    @endforeach

    @if ($related)
        <section class="mx-auto max-w-7xl px-5 pb-16">
            <h2 class="font-heading text-3xl font-semibold md:text-4xl">Related posts</h2>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($related as $r)
                    <h2 class="m-0 text-base font-medium"><a href="{{ $r['href'] }}" class="block h-full rounded-2xl bg-ink-2 p-6 leading-snug transition hover:bg-white/10">{{ $r['title'] }}</a></h2>
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
