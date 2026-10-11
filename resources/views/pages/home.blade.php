@php
    use App\Support\SectionSplitter;
    $d = $page->data ?? [];
    $parts = SectionSplitter::byH2($page->body_html ?? '');
    $by = collect($parts['sections'])->keyBy(fn ($s) => match (true) {
        str_starts_with($s['title'], 'Superior') => 'intro',
        str_starts_with($s['title'], 'What Makes') => 'why',
        str_starts_with($s['title'], 'Our Chauffeurs') => 'chauffeurs',
        str_starts_with($s['title'], 'Areas We Serve') => 'areas',
        str_starts_with($s['title'], 'Frequently') => 'faq',
        default => $s['slug'],
    });
@endphp
<x-layout :page="$page">
    {{-- Hero --}}
    <section class="relative isolate flex min-h-[620px] items-center overflow-hidden bg-ink">
        <video class="absolute inset-0 -z-20 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
            <source src="{{ $d['hero']['video'] ?? '' }}" type="video/mp4">
        </video>
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black/85 via-black/60 to-black/20"></div>
        <div class="mx-auto w-full max-w-7xl px-5 py-20">
            <h1 class="max-w-3xl font-display text-4xl font-semibold leading-tight md:text-5xl">{{ $page->h1 }}</h1>
            <p class="mt-5 max-w-2xl font-heading text-lg text-gold">{{ $d['hero']['sub'] ?? '' }}</p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <button type="button" @click="quote = true" class="btn btn-gold">Get a Free Quote</button>
                <span class="text-sm text-white/60">Or</span>
                <a href="/booking/" class="btn btn-ghost">Quick Booking</a>
            </div>
        </div>
    </section>

    {{-- Booking --}}
    <section class="bg-ink" id="book">
        <div class="mx-auto max-w-5xl px-5 py-16 text-center">
            <h2 class="font-heading text-3xl font-medium">{{ $d['booking_title'] ?? 'Make a booking' }}</h2>
            <iframe src="{{ config('site.booking_embed') }}" title="Online reservations" loading="lazy" class="mt-8 h-[900px] w-full rounded-2xl border-0 bg-white"></iframe>
        </div>
    </section>

    {{-- Services --}}
    <section class="section">
        <p class="eyebrow">{{ $d['services']['eyebrow'] ?? '' }}</p>
        <h2 class="mt-3 max-w-3xl font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $d['services']['title'] ?? '' }}</h2>
        <p class="mt-4 max-w-3xl text-white/75">{{ $d['services']['intro'] ?? '' }}</p>
        <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($d['services']['cards'] ?? [] as $c)
                <li class="group relative overflow-hidden rounded-3xl">
                    <a href="{{ $c['href'] }}" class="block">
                        <img src="{{ $c['img'] }}" alt="{{ $c['alt'] ?? $c['title'] }}" width="550" height="550" loading="lazy" class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent"></div>
                        <h2 class="absolute bottom-5 left-5 right-5 font-heading text-xl font-semibold text-gold">{{ $c['title'] }}</h2>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Fleet --}}
    <section class="bg-ink-2">
        <div class="section">
            <p class="eyebrow">{{ $d['fleet']['eyebrow'] ?? '' }}</p>
            <h2 class="mt-3 max-w-3xl font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $d['fleet']['title'] ?? '' }}</h2>
            <p class="mt-4 max-w-3xl text-white/75">{{ $d['fleet']['intro'] ?? '' }}</p>
            <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($d['fleet']['cards'] ?? [] as $c)
                    <li class="overflow-hidden rounded-3xl bg-ink">
                        <img src="{{ $c['img'] }}" alt="{{ $c['alt'] ?? $c['title'] }}" width="500" height="357" loading="lazy" class="aspect-[7/5] w-full object-cover">
                        <div class="p-5">
                            <h2 class="font-heading text-lg font-semibold">{{ $c['title'] }}</h2>
                            <h3 class="mt-1 text-sm text-gold">{{ $c['cat'] }}</h3>
                            <a href="{{ $c['href'] }}" class="btn btn-ghost mt-4 !px-4 !py-1.5">Book</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Booking band --}}
    <section class="bg-gold/10">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-5 py-14 md:flex-row md:items-center">
            <div>
                <h2 class="font-heading text-3xl font-semibold">{{ $d['band']['title'] ?? 'Book Your Private Chauffeur' }}</h2>
                <p class="mt-2 text-white/75">{{ $d['band']['sub'] ?? '' }}</p>
            </div>
            <a href="/booking/" class="btn btn-gold">Book Now</a>
        </div>
    </section>

    {{-- Intro prose --}}
    @isset($by['intro'])
        <section class="section grid gap-10 lg:grid-cols-[1fr_2fr]">
            <div>
                <p class="eyebrow">{{ $d['intro_eyebrow'] ?? '' }}</p>
                <h2 class="mt-3 font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $by['intro']['title'] }}</h2>
            </div>
            <div class="prose-cs">{!! $by['intro']['html'] !!}</div>
        </section>
    @endisset

    <x-reviews />

    {{-- Stats --}}
    <section class="bg-cream/10">
        <ul class="mx-auto grid max-w-7xl gap-4 px-5 py-10 sm:grid-cols-2 lg:grid-cols-6">
            @foreach ($d['stats'] ?? [] as $s)
                <li class="flex items-start gap-3 text-sm font-medium"><span class="mt-0.5 text-gold" aria-hidden="true">&#10003;</span>{{ $s }}</li>
            @endforeach
        </ul>
    </section>

    {{-- Why us --}}
    @isset($by['why'])
        @php($why = SectionSplitter::byH3($by['why']['html']))
        <section class="section">
            <h2 class="max-w-3xl font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $by['why']['title'] }}</h2>
            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($why['items'] as $item)
                    <div class="rounded-3xl bg-ink-2 p-7">
                        <h3 class="font-heading text-lg font-semibold text-gold">{{ $item['title'] }}</h3>
                        <div class="prose-cs mt-3">{!! $item['html'] !!}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endisset

    {{-- Chauffeurs --}}
    @isset($by['chauffeurs'])
        @php($ch = SectionSplitter::byH3($by['chauffeurs']['html']))
        <section class="bg-ink-2">
            <div class="section">
                <h2 class="max-w-3xl font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $by['chauffeurs']['title'] }}</h2>
                <div class="mt-10 grid gap-5 md:grid-cols-2">
                    @foreach ($ch['items'] as $item)
                        <div class="rounded-3xl bg-ink p-7">
                            <h3 class="font-heading text-lg font-semibold text-gold">{{ $item['title'] }}</h3>
                            <div class="prose-cs mt-3">{!! $item['html'] !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endisset

    {{-- Areas --}}
    @isset($by['areas'])
        @php($ar = SectionSplitter::byH3($by['areas']['html']))
        <section class="section">
            <h2 class="max-w-3xl font-heading text-3xl font-semibold leading-tight md:text-4xl">{{ $by['areas']['title'] }}</h2>
            <div class="prose-cs mt-6 max-w-3xl">{!! $ar['lead'] !!}</div>
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @foreach ($ar['items'] as $item)
                    <div class="rounded-3xl bg-ink-2 p-7">
                        <h3 class="font-heading text-lg font-semibold text-gold">{{ $item['title'] }}</h3>
                        <div class="prose-cs mt-3">{!! $item['html'] !!}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endisset

    {{-- FAQ --}}
    @isset($by['faq'])
        <section class="mx-auto max-w-4xl px-5 pb-24">
            <h2 class="mb-8 font-heading text-3xl font-semibold md:text-4xl">{{ $by['faq']['title'] }}</h2>
            <x-faq :items="SectionSplitter::byH3($by['faq']['html'])['items']" />
        </section>
    @endisset
</x-layout>
