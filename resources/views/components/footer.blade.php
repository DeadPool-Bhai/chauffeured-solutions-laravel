<footer class="border-t border-white/10 bg-ink text-white/80">
    <div class="mx-auto max-w-7xl px-5 py-16">
        <div class="mb-12 flex flex-col items-start justify-between gap-6 rounded-3xl bg-ink-2 p-8 md:flex-row md:items-center">
            <div>
                <p class="font-heading text-2xl font-semibold text-white">Get in touch</p>
                <p class="mt-1 text-white/70">&ldquo;Contact us today and experience luxury on the go.&rdquo;</p>
            </div>
            <a href="/contact-us/" class="btn btn-gold">Get in Touch</a>
        </div>

        <div class="grid gap-10 md:grid-cols-4">
            <div>
                <img src="{{ config('site.logo') }}" alt="{{ config('site.name') }}" width="160" height="43" class="h-10 w-auto" loading="lazy">
                <p class="mt-4 text-sm">{{ config('site.legal_name') }}<br>ABN: {{ config('site.abn') }}</p>
                <ul class="mt-4 flex gap-4 text-sm">
                    @foreach (config('site.social') as $label => $url)
                        <li><a href="{{ $url }}" rel="noopener" target="_blank" class="hover:text-gold">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="font-heading text-base font-semibold text-gold">Services</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach (config('site.footer_services') as [$label, $href])
                        <li><a href="{{ $href }}" class="hover:text-gold">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="md:col-span-2">
                <h2 class="font-heading text-base font-semibold text-gold">Contact Us</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="mailto:{{ config('site.email') }}" class="hover:text-gold">{{ config('site.email') }}</a></li>
                    <li><a href="{{ config('site.phone_href') }}" class="hover:text-gold">{{ config('site.phone') }}</a></li>
                    <li><a href="{{ config('site.mobile_href') }}" class="hover:text-gold">{{ config('site.mobile') }}</a></li>
                    <li>{{ config('site.address') }}</li>
                </ul>
                <a href="/booking/" class="btn btn-gold mt-6">Quick Booking</a>
            </div>
        </div>

        <div class="mt-12 flex flex-col justify-between gap-2 border-t border-white/10 pt-6 text-xs text-white/60 md:flex-row">
            <p>Copyright &copy; {{ config('site.legal_name') }}</p>
            <p>Tech Solution: <a href="https://stechs.com.au/" rel="noopener" class="hover:text-gold">S-Tech Solutions</a></p>
        </div>
    </div>

    <a href="{{ config('site.whatsapp') }}" rel="noopener" target="_blank" class="fixed bottom-5 left-5 z-30 flex items-center gap-2 rounded-full bg-[#25d366] px-4 py-3 text-sm font-semibold text-white shadow-lg">
        <span aria-hidden="true">&#9993;</span> Chat with a Chauffeur!
    </a>
</footer>
