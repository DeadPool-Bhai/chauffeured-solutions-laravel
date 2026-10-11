# Build status (11 Oct 2026)

Supersedes the data model in migration-plan.md: pages and posts share one `pages` table (`kind` = page|post, `type` = home, suburb, post, page, utility, legal, blog_index).

## Done
- Router: redirect rules, exact `canonical_path`, slash/no-slash twin 301 (query kept), 404 otherwise. 410 rules supported.
- Importer (`import:wordpress`): 140 published URLs, cleaned HTML, live titles/descriptions snapshotted, 12 JSON-LD blocks carried verbatim, redirects flattened, internal links pointed at final targets.
- Templates: home, suburb, service (any page with the quote form: services, fleet, tours, airport/hotel pages, with related-post cards), post, blog index (live list of all posts, noindex as live), default (legal, thank-you, utility). Quote form with honeypot, throttle, reCAPTCHA v3, saved lead, queued emails, redirect to /thank-you-f4-quote-request/.
- SEO output: sitemap_index/page/post sitemaps (parity with live via `php artisan seo:parity`), robots.txt, canonical, OG/Twitter, GTM + GA4 from env.
- Filament admin (`/admin`, path set by ADMIN_PATH): pages (SEO fields, status), redirects (rejects self-loops and chains), leads (read-only, stats). Changing a page URL creates a 301 automatically.
- Lead emails default to asker.asraf@stechs.com.au; switch LEAD_NOTIFY_TO to paul@ and booking@ at cutover.
- 41 tests passing. Every published URL renders 200 with one H1, apart from the two that have redirect rules on live (/holden-caprice/, /corporate-sedan/).

## Not done
- Wedding forms 7 and 9, /booking-confirmed/ page, category archive /category/corporate/.
- AI module, llms.txt, Release 1.1 schema.
- Media copy to public/wp-content/uploads (gitignored, deploy separately).

## Deploy on Hostinger
1. `composer install --no-dev -o`, `npm ci && npm run build` (public/build is not committed).
2. `.env`: APP_URL, DB, MAIL_*, QUEUE_CONNECTION=database, CACHE_STORE=database, ADMIN_PATH, LEAD_NOTIFY_TO, RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET, GTM_ID, GA4_ID.
3. `php artisan migrate --force && php artisan import:wordpress <export.xml> --snapshot=database/seeders/data/seo_snapshot.json && php artisan db:seed --force && php artisan optimize`.
4. Cron every minute: `php artisan schedule:run`; second entry `php artisan queue:work --stop-when-empty --max-time=55` (lead emails depend on it).
5. Create the first admin: `php artisan make:filament-user`.
6. Staging: noindex plus HTTP auth until cutover.

## Notes
- Any user row can open the admin (`canAccessPanel` returns true). Fine while only staff have accounts; tighten before adding other user types.
- Live defects carried over on purpose for parity are listed in the PR description.
