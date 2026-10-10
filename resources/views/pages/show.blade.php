<x-layout :page="$page">
    <article class="mx-auto max-w-4xl px-5 py-16 md:py-24">
        @if ($page->h1)
            <h1 class="font-display text-4xl font-semibold leading-tight md:text-5xl">{{ $page->h1 }}</h1>
        @endif
        <div class="prose-cs mt-8">{!! \App\Support\Shortcodes::expand($page->body_html ?? '') !!}</div>
    </article>
</x-layout>
