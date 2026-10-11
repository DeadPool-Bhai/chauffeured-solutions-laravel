@php
    $posts = \App\Models\Page::live()->where('kind', 'post')->orderByDesc('published_at')->get(['id', 'canonical_path', 'title', 'h1', 'excerpt', 'featured_image', 'published_at']);
@endphp
<x-layout :page="$page">
    <section class="mx-auto max-w-7xl px-5 py-16 md:py-24">
        <h1 class="font-display text-4xl font-semibold leading-tight md:text-5xl">{{ $page->h1 ?: 'Blog' }}</h1>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="overflow-hidden rounded-2xl bg-ink-2">
                    @if ($post->featured_image)
                        <a href="{{ $post->canonical_path }}"><img src="{{ $post->featured_image }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover"></a>
                    @endif
                    <div class="p-6">
                        <p class="text-xs text-white/50">{{ $post->published_at?->format('j F Y') }}</p>
                        <h2 class="mt-2 text-lg font-semibold leading-snug"><a href="{{ $post->canonical_path }}">{{ $post->h1 ?: $post->title }}</a></h2>
                        <a href="{{ $post->canonical_path }}" class="mt-4 inline-block text-sm text-gold">Read more</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-layout>
