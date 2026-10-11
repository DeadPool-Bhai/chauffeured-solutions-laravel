import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Quote form: fills hidden context fields, then gets a reCAPTCHA v3 token before submitting.
Alpine.data('quoteForm', (siteKey) => ({
    busy: false,
    init() {
        this.$refs.title.value = document.title;
        this.$refs.path.value = location.pathname;
    },
    async submit(event) {
        if (!siteKey || !window.grecaptcha) return; // no key configured: plain submit
        event.preventDefault();
        if (this.busy) return;
        this.busy = true;
        try {
            await new Promise((resolve) => window.grecaptcha.ready(resolve));
            this.$refs.token.value = await window.grecaptcha.execute(siteKey, { action: 'quote' });
        } catch (e) {
            // Submit anyway. The server decides.
        }
        this.$el.submit();
    },
}));

Alpine.start();
