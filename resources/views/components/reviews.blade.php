@php($reviews = config('reviews'))
<section class="py-10" aria-label="Google reviews">
    <div class="mx-auto max-w-7xl px-5">
        <div class="mb-6 flex flex-wrap items-center gap-3 text-sm">
            <span class="font-heading font-semibold text-white">Chauffeured Solutions</span>
            <span class="text-gold" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <span class="text-white/70">{{ number_format($reviews['rating'], 1) }} &middot; {{ $reviews['count'] }} Google reviews</span>
        </div>
        <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($reviews['items'] as $r)
                <li class="rounded-2xl bg-ink-2 p-5">
                    <p class="text-gold" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</p>
                    <p class="mt-2 text-sm leading-6 text-white/80">{{ $r['text'] }}</p>
                    <p class="mt-3 text-xs font-semibold text-white">{{ $r['name'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
