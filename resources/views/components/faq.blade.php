@props(['items'])
<div class="divide-y divide-white/10 rounded-2xl border border-white/10">
    @foreach ($items as $i => $item)
        <div x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
            <h3>
                <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-heading text-base font-medium text-white">
                    <span>{{ $item['title'] }}</span>
                    <span class="text-gold" x-text="open ? '−' : '+'" aria-hidden="true"></span>
                </button>
            </h3>
            <div x-show="open" x-cloak class="prose-cs px-5 pb-5">{!! $item['html'] !!}</div>
        </div>
    @endforeach
</div>
