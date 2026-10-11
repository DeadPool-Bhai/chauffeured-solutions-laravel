@php
    // Each menu entry is [label, href, children?]; pad so every level has a children list.
    $norm = function (array $items) use (&$norm) {
        return array_map(fn ($i) => [$i[0], $i[1], isset($i[2]) ? $norm($i[2]) : []], $items);
    };
    $nav = $norm(config('site.nav'));
@endphp
<header x-data="{ open: false }" class="relative z-40 bg-ink text-white">
    {{-- Top bar --}}
    <div class="hidden border-b border-white/10 text-xs lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-2">
            <ul class="flex items-center gap-4 text-white/70">
                @foreach (config('site.social') as $label => $url)
                    <li><a href="{{ $url }}" rel="noopener" target="_blank" class="hover:text-gold">{{ $label }}</a></li>
                @endforeach
            </ul>
            <ul class="flex items-center gap-6">
                @foreach (config('site.topbar') as [$label, $href])
                    <li>
                        @if ($href === '#quote')
                            <button type="button" @click="quote = true" class="hover:text-gold">{{ $label }}</button>
                        @else
                            <a href="{{ $href }}" class="hover:text-gold">{{ $label }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4">
        <a href="/" aria-label="{{ config('site.name') }} home"><img src="{{ config('site.logo') }}" alt="{{ config('site.name') }}" width="160" height="43" class="h-10 w-auto"></a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-7 lg:flex" aria-label="Primary">
            @foreach ($nav as [$label, $href, $children])
                <div class="group relative">
                    <a href="{{ $href }}" class="py-3 font-heading text-[13px] font-medium uppercase tracking-widest hover:text-gold">{{ $label }}@if ($children) <span aria-hidden="true" class="text-gold">&#9662;</span>@endif</a>
                    @if ($children)
                        <ul class="invisible absolute left-0 top-full z-50 min-w-64 border-t-2 border-gold bg-ink-2 py-2 opacity-0 shadow-xl transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                            @foreach ($children as [$cl, $ch, $grand])
                                <li class="group/sub relative">
                                    <a href="{{ $ch }}" class="flex items-center justify-between px-4 py-2 text-sm hover:text-gold">{{ $cl }}@if ($grand) <span aria-hidden="true" class="text-gold">&#9656;</span>@endif</a>
                                    @if ($grand)
                                        <ul class="invisible absolute left-full top-0 min-w-64 border-t-2 border-gold bg-ink-2 py-2 opacity-0 shadow-xl transition group-hover/sub:visible group-hover/sub:opacity-100 group-focus-within/sub:visible group-focus-within/sub:opacity-100">
                                            @foreach ($grand as [$gl, $gh])
                                                <li><a href="{{ $gh }}" class="block px-4 py-2 text-sm hover:text-gold">{{ $gl }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ config('site.phone_href') }}" class="btn btn-gold hidden sm:inline-flex">{{ config('site.phone') }}</a>
            <button type="button" class="lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile nav --}}
    <nav x-show="open" x-cloak class="border-t border-white/10 bg-ink-2 px-5 py-4 lg:hidden" aria-label="Mobile">
        <ul class="space-y-1">
            @foreach ($nav as [$label, $href, $children])
                <li x-data="{ sub: false }">
                    <div class="flex items-center justify-between">
                        <a href="{{ $href }}" class="block flex-1 py-2 font-heading text-sm uppercase tracking-widest">{{ $label }}</a>
                        @if ($children)
                            <button type="button" @click="sub = !sub" class="px-3 text-gold" aria-label="Toggle {{ $label }}">&#9662;</button>
                        @endif
                    </div>
                    @if ($children)
                        <ul x-show="sub" x-cloak class="ml-3 space-y-1 border-l border-gold/40 pl-4 text-sm text-white/80">
                            @foreach ($children as [$cl, $ch, $grand])
                                <li><a href="{{ $ch }}" class="block py-1.5">{{ $cl }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="mt-4 flex gap-3">
            <a href="{{ config('site.phone_href') }}" class="btn btn-gold flex-1">{{ config('site.phone') }}</a>
            <button type="button" @click="quote = true; open = false" class="btn btn-ghost flex-1">Get Quote</button>
        </div>
    </nav>
</header>
