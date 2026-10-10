<div x-show="quote" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="quote = false" role="dialog" aria-modal="true" aria-label="Get a free quote">
    <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-ink-2 p-8">
        <button type="button" class="absolute right-4 top-4 text-2xl text-white/70 hover:text-gold" @click="quote = false" aria-label="Close">&times;</button>
        <h2 class="font-heading text-2xl font-semibold">Get a Free Quote</h2>
        <div class="mt-6"><x-quote-form /></div>
    </div>
</div>
