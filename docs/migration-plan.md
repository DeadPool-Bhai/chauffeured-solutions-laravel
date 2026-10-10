# Chauffeured Solutions: Laravel Migration Plan

Prepared 10 Oct 2026 for Asker Asraf, S-Tech Solutions. Source: live crawl of chauffeuredsolutions.com.au plus the Search Console "Generative AI features" export (last 3 months).

## 1. Decisions locked in

- **Client:** Chauffeured Solutions, Sydney. Live site is WordPress with Elementor.
- **Approach:** Phase 1 is a like-for-like migration. Same URLs, copy, headings, meta data and look. A redesign comes later, so any ranking change can be traced to one cause.
- **Stack:** Laravel 12, Blade, Tailwind, Alpine.js, Filament admin. Every page is server-rendered HTML.
- **Booking:** stays as the Limo Anywhere embed on /booking/.
- **Leads:** the contact form saves to the database and emails the client. No CRM.
- **Editor:** Shakawath.
- **Hosting:** Hostinger Cloud (hPanel). Database queue and cache, cron every minute, no Redis, no long-running workers.
- **AI features:** content assistant, draft-only page generator, SEO audit, performance monitor, refresh suggestions.

## 2. What is on the live site today

Crawled 10 Oct 2026 using the WordPress REST API and each page's HTML. There are 140 URLs: 98 pages and 42 blog posts. All return HTTP 200.

| Group | URLs | Examples |
|---|---|---|
| Home | 1 | / |
| Core service pages | 14 | airport-transfers-sydney, wedding-chauffeur, corporate-hire, corporate-chauffeur, hotel-transfers, event-transfers, show-concert-transfers, cruise, funeral, race day, vip, services, NYE page, hire-private-jet |
| Airport and hotel transfer pages | 10 | syd-airport-to-crown-sydney, syd-airport-shangri-la-hotel-sydney, four-seasons-hotel, 7 more |
| Vehicle and fleet pages | 22 | fleet, people-mover, mercedes-sprinter-van-hire-sydney, 12-seater, 53-seater, mini-buses, luxury category pages, 9 individual car models. Two of the 22 redirect (see section 3) |
| Tours | 7 | Sydney, Blue Mountains, Hunter Valley, Gold Coast, Melbourne, Cairns, tours index |
| Suburb pages (/service-area/...) | 31 | Cherrybrook, Collaroy, Windsor, Sydney CBD, Central Coast, Blue Mountains and others |
| Company and legal | 7 | about-us, contact-us, booking, blog index, how-to, terms, privacy |
| Thank-you, login and test pages | 6 | thank-you, thank-you-f4-quote-request, wedding-chauffeur-booking-form, login, test-login, dont-try-this-again |
| Blog posts | 42 | 36 under /blog/, 6 at the site root (for example your-complete-guide-to-wedding-car-hire-sydney) |
| **Total** | **140** | |

**Protected tier (Search Console, last 3 months).** The top 10 pages hold about 76% of impressions: home, syd-airport-to-crown-sydney, people-mover, wedding-chauffeur, airport-transfers-sydney, syd-airport-shangri-la-hotel-sydney, blog/top-fancy-cars-for-weddings, corporate-hire, four-seasons-hotel, mercedes-sprinter-van-hire-sydney. These get byte-level comparison at QA. This export has impressions only, so it should be re-ranked with the standard Performance report (clicks, 16 months) when available.

## 3. Findings that change the build

1. **Slash handling is inconsistent.** 13 URLs use a canonical with no trailing slash (hotel-transfers, event-transfers, the NYE page, richmond-limousine-hire, and 9 blog posts such as blog/top-fancy-cars-for-weddings). Every other page canonicalises with a slash. For these 13, both the slash and no-slash versions return 200 today, and Search Console shows Google has seen both (mercedes-sprinter-van-hire-sydney appears both ways). Laravel's default `.htaccess` strips trailing slashes, which would break the 100+ slash URLs unless that rule is removed.
2. **Redirects are managed in Rank Math: 38 active rules, 1 in the trash.** Read from the admin on 10 Oct 2026 (nothing was changed). The busiest by hits: /corporate-sedan/ (1,524), /sydney-airport-luxury-transfer-offer/ (1,115), /wedding-limo/ (962), /our-fleet/ (926), /holden-caprice/ (708), /corporate-cars/hotel-transfers/ (690), /top-fancy-cars-for-weddings-arrive-in-luxury/ (627). I tested every rule. Most resolve in one hop to a 200 page. Three problems:
   - **Chain:** /airport-transfers/ (122 hits) goes to /sydney-airport-luxury-transfer-offer/, which goes to /airport-transfers-sydney/.
   - **Chain:** /discover-most-unique-and-stylish-wedding-cars-in-sydney/ (569 hits) targets a URL that itself redirects to the live post, whose slug ends in -sydney-2.
   - **Dead end:** /corporate-cars/security-escorted-services/ (30 hits) targets /security-escorted-service/ (page ID 552), which is a draft, so visitors get a 404. Its canonical also points to `/?page_id=552`.
   Two rules return 410 (apple-app-site-association, favicon.ico). All 38 rules get imported into the new redirect manager with chains flattened to the final URL.
3. **Noindex pages (8):** terms-and-conditions, thank-you, thank-you-f4-quote-request, wedding-chauffeur-booking-form, login, test-login, dont-try-this-again, and the **/blog/ index**. The utility ones are correct. The blog index being noindex looks like an oversight. Keep it as is in Phase 1 and fix it in Phase 2.
4. **/hire-private-jet/** is indexable and has the title "Thank You - Chauffeured Solutions", the same as /thank-you/. Likely a leftover duplicate. Needs a decision.
5. **Titles and descriptions run long.** About 59 of 140 titles exceed 65 characters, including the home page (68) and nearly every blog post. 45 meta descriptions exceed 160 characters, 9 are under 70, and 2 are missing (/corporate-chauffeur/, /test-login/). Phase 1 keeps them identical. Fixing them is Phase 2, with before and after tracking.
6. **Schema is thin, and hand-built.** Only 13 of 140 URLs output JSON-LD (LocalBusiness, Service, FAQPage, BreadcrumbList, WebPage). The other 127 output none, including all 42 posts, all 31 suburb pages and all vehicle pages. This is the biggest AEO and GEO gap. The export shows where the existing schema lives: it is not Rank Math. It sits in 12 Elementor Pro Custom Code snippets (1 homepage LocalBusiness block plus 11 service-page blocks, each 11 to 19 KB), hard-wired to page IDs (12 blocks against 13 URLs, so the 13th URL gets its schema from somewhere else, probably the corporate landing plugin; to confirm). Each service block repeats the full LocalBusiness and WebSite definitions. Known quirks: the homepage `telephone` field holds two numbers in one string, and the Event Transfers and Hotel Transfers blocks reference the slash URL while the canonical has no slash. Phase 1 carries all 12 blocks over verbatim so Google sees no change.
7. **/booking/ has no H1.** It is an Elementor canvas page holding only the Limo Anywhere iframe, so it has no crawlable copy and no schema. The Laravel page will carry server-rendered text around the embed.
8. **Content is not thin.** Every page has over 300 words of main-area text (this count includes some navigation text, so treat it as a floor check).
9. **robots.txt is minimal** (blocks /wp-admin/ only, points to sitemap_index.xml). The sitemap index lists two sitemaps: page-sitemap.xml and post-sitemap.xml.
10. **Tracking is sitewide via Elementor Custom Code:** GTM container GTM-KBKWMQW5 (head and body snippets) and GA4 property G-0YJM2KDWVT. Both carry over unchanged. **Conversion change (decided 10 Oct 2026):** the primary conversion will become a confirmed booking in Limo Anywhere. That happens inside the embed on another domain, so GTM and GA4 on the site cannot see it by default. Plan: have Limo Anywhere send customers back to a new noindex page on the site (for example /booking-confirmed/) after a confirmed booking, and fire the GA4 conversion from that page view in GTM. If Limo Anywhere cannot redirect, use its own tracking-code or conversion field and test cross-domain linking. The quote-form thank-you page stays as a secondary conversion. The iframe on almost every page is the GTM noscript tag. Google Maps iframes sit on 18 pages, and the Limo Anywhere embed (book.mylimobiz.com) is on /booking/.
11. **Drafts exist.** 11 draft pages (including Home Demo, a cloned Airport Transfers page, Oasis Live '25 Limo Transfers and Security Escorted Service) and 10 draft posts. They stay out of the public import. They can be imported as drafts if you want them.
12. **The WordPress export is usable** (checked 10 Oct 2026). It matches the crawl exactly: 98 published pages, 42 published posts, 11 draft pages, 10 draft posts, 411 media files, 10 categories, 7 tags, 93 menu items. Details that shape the importer:
   - **Body content:** the database holds the rendered HTML for nearly every page (average 875 words). Four pages have none: /corporate-chauffeur/ (rendered by the "CS Corporate Landing Page" plugin template), /hire-private-jet/, /login/ and /test-login/. These need the plugin's source or a scrape of the live HTML.
   - **Forms:** Fluent Forms shortcodes appear on 69 pages. The three form definitions were exported separately and are detailed in section 5a.
   - **Reviews:** a Trustindex Google reviews shortcode (free, script-based) is on 45 pages.
   - **Shared block:** shortcode [8226] is on 18 pages (an Elementor template), probably the repeated section on the venue and tour pages. To be inspected.
   - **SEO fields:** Rank Math holds an explicit title on only 54 items and an explicit description on 115. The rest come from Rank Math templates. For Phase 1 the importer will snapshot each page's rendered title and description from the live site and store them as explicit values, so nothing depends on reproducing the templates. 96 items carry a focus keyword, which feeds the AI assistant and the monitor.
   - **Custom permalinks:** 71 items carry one, and the 13 no-slash URLs all come from here, matching the crawl.
   - **Internal Link Juicer:** keyword link rules exist on 4 pages (Viano, People Mover, Sprinter, Corporate Chauffeur).
   - **Media:** 348 files from 2025 and 63 from 2026, all under /wp-content/uploads.
   - **Categories:** 40 of 42 posts are in "Blog". /category/corporate/ is a live archive and is noindex.
13. **Plugins that shape the build** (from the admin plugin list):
   - **Rank Math SEO:** titles, descriptions, schema, redirects and sitemaps. It is Rank Math, not Yoast. Rank Math normally outputs schema, so check whether schema output is switched off in its settings.
   - **Custom Permalinks:** the reason for the slash and no-slash mix. Each page can carry its own path.
   - **Internal Link Juicer:** inserts internal links automatically when pages render. The importer would freeze those as hard-coded links. Phase 1 keeps them frozen so links stay identical, and Phase 2 replaces them with a link manager in the admin.
   - **Fluent Forms Pro and FluentSMTP:** the contact and quote forms and their email delivery.
   - **Chaty:** chat and call buttons. **Widgets for Google Reviews:** review display, which is a schema opportunity (AggregateRating, only if the reviews are genuinely on the page).
   - **Wordfence and WPS Hide Login:** security and the custom admin URL.
   - **Imagify:** image compression. **WP Rocket:** installed but inactive.
   - Elementor Pro and several Elementor add-ons.

## 4. URL and redirect rules

- **One source of truth:** each page row stores `canonical_path`, copied exactly from the live `<link rel="canonical">`. The router serves that path with 200.
- **Variants:** the slash or no-slash twin of any canonical path gets a 301 to the canonical. This is a small tightening of the live behaviour, where both return 200, and it matches what the canonical tag already tells Google.
- **Remove Laravel's trailing-slash redirect** from `public/.htaccess`.
- **Host and protocol:** force https and the non-www host (canonical tags use non-www). Confirm before cutover.
- **Redirect manager:** a Filament screen backed by a `redirects` table (from, to, status, hit count, notes). Loaded from the 38 Rank Math rules (chains flattened to the final URL) plus the slash-variant rules. Editing a page slug auto-creates a 301 and warns the editor.
- **Media:** keep `/wp-content/uploads/...` paths working by copying the year folders into `public/wp-content/uploads`. Image URLs are in the sitemap and indexed in Google Images. The uploads zip was checked on 10 Oct 2026 (3,869 files, 181 MB, integrity test passed). The live pages and templates reference 542 distinct upload URLs, and 541 are in the zip. The one missing file is an Elementor thumbnail cache image (`2025/11/elementor/thumbs/...`), which can be ignored. What gets deployed:
   - **Deploy (about 3,050 files, 134 MB):** all year folders (2025/05 onward). That includes 533 directly referenced files, 1,198 other sizes and .webp copies of referenced images, and 1,322 files no live page references. The unreferenced ones stay in Phase 1, because some may still be indexed in Google Images and deleting is not reversible for ranking purposes.
   - **Do not deploy:** `backup/` (430 duplicate files, 33.5 MB), `elementor/` (generated CSS and font cache), `template-kits/`, and plugin folders (happyaddons, rank-math, burst, wp-file-manager-pro, the Trustindex CSS file).
   - **Performance follow-up (Phase 2):** the home page hero is a 4.8 MB video (`2025/05/home-header.mp4`), the likeliest drag on load speed. Imagify's .webp copies are reused through `<picture>` tags.
- **Old WordPress paths** (feeds, `?s=` search, category and tag archives, author pages, attachment pages, `/page/2/` pagination, `/wp-json/`, `/wp-login.php`): list from the crawl, decide per path between 301 to the closest page and 410. None are in the sitemap today.
- **Sitemaps:** serve `/sitemap_index.xml`, `/page-sitemap.xml` and `/post-sitemap.xml` at the same paths, so the sitemap already submitted in Search Console keeps working.

## 5. Laravel architecture

**Content tables**
- `pages` (98 rows at import): type, canonical_path, title, h1, body_html, template, status, published_at, modified_at, SEO fields (seo_title, meta_description, robots, canonical_override, og_image), `schema_overrides` JSON.
- `posts` (42 rows): same SEO fields plus excerpt, featured_image, published_at, modified_at (dates preserved exactly, since they feed Article schema).
- `faqs`: polymorphic, attached to any page or post, feeds both the visible FAQ block and FAQPage schema.
- Structured sets: `venues` (airport-to-venue pages: venue, address, distance_km, drive_minutes), `vehicles` (class, seats, luggage), `suburbs` (name, region, postcode, lat/long), `tours`. Each links to its page. These fields drive schema and give the AI generator facts it must use rather than invent.

**Operational tables:** `redirects`, `leads`, `media`, `settings` (business details, llms.txt, robots), `seo_audits`, `seo_issues`, `gsc_metrics`, `ai_drafts`.

## 5a. Forms and lead handling (from the Fluent Forms exports)

| Form | Where | Fields | After submit |
|---|---|---|---|
| **4: Get A Free Quote [Lead Form]** | 67 pages. This is the main conversion form | Name (first name and email and phone required), pick-up date and time, flight or airport note, pick-up address, drop-off address (required), passengers and luggage count (required), reCAPTCHA v3, 3 hidden fields | Redirects to /thank-you-f4-quote-request/ (noindex) |
| **9: Get A Free Quote [Wedding Chauffeur]** | 1 page | Name, email, phone, wedding date (required), number of cars, notes, reCAPTCHA v3, same 3 hidden fields | Redirects to the same thank-you page |
| **7: Wedding Car Hire** | /wedding-chauffeur-booking-form/ (noindex) | 47-field wedding itinerary: bride and groom details and pick-ups, ceremony, photo and reception locations, gateway car, special instructions, terms checkbox, reCAPTCHA v3 | Same-page confirmation message |

- **Hidden fields on forms 4 and 9:** the page title the form sits on and the HTTP referer both carry over. The page title tells the client which page generated the lead. The old `hidden_2` URL parameter is dropped (decided 10 Oct 2026).
- **Thank-you URL:** /thank-you-f4-quote-request/ is very likely the GTM conversion trigger. The URL stays exactly as is, and Laravel redirects there after a successful submit (a POST, redirect, GET pattern).
- **Email routing per form:** an admin notification to the client's main inbox, a confirmation email to the customer, and a second copy to a booking inbox on form 4. The old "Duplicate Copy" notification to a personal Gmail address is dropped (decided 10 Oct 2026), so leads go only to the client's own inboxes.
- **Laravel build:** one `Lead` table (form key, payload JSON, source page title, referer, hidden_2, IP, user agent, timestamps), three Blade form components with server-side validation, honeypot plus reCAPTCHA v3 (the existing choice), queued notification and confirmation emails, and a Filament screen to view and export leads. The existing confirmation copy and email subjects carry over. Field names are kept so the client's inbox emails look familiar.
- Address autocomplete is switched off in the current forms, so there is no Places API dependency.

**Blade templates:** home, service, venue-transfer, vehicle, fleet-index, tour, suburb, blog-index, blog-post, booking (server copy plus Limo Anywhere embed), contact, standard (legal), thank-you.

**Import:** an `import:wordpress` Artisan command reads the live REST API, cleans Elementor markup into plain semantic HTML (headings, paragraphs, lists, images, links preserved), and records the original URL, title, description and canonical for each row. It can be re-run until the cutover freeze.

**Design:** rebuilt in Tailwind from the current site so it looks the same. Screenshots of each template are needed before building (open item).

## 6. SEO, AEO and GEO spec

- **JSON-LD on every template:** LocalBusiness with Sydney service area and the airport and suburb areas served, Service (service, venue and tour pages), FAQPage, BreadcrumbList, Article (posts), Vehicle or Product (fleet models). Generated from table fields, with per-page overrides.
- **Answer-first content:** a short direct-answer summary at the top of service, venue and suburb pages, and an FAQ block written as one to two sentence answers.
- **llms.txt** and a managed robots.txt, both editable in the admin, stating which AI crawlers are allowed.
- **Meta controls** per page: title, description, robots, canonical, Open Graph and Twitter image.
- **Technical:** XML sitemaps at the existing paths, canonical tags, image lazy loading and responsive sizes, full-page cache, and a Core Web Vitals check in the audit.
- **Phase 1 rule:** titles, descriptions, headings, body copy and the 12 existing schema blocks are identical to live.
- **New schema ships after cutover, not on cutover day.** It is additive and invisible, but if rankings move in the first weeks you would not know whether the platform change or the new schema caused it. Release it 2 to 4 weeks after the migration settles, in batches (service pages first, then venues, suburbs, vehicles, posts), with the single `@graph` structure: LocalBusiness defined once and referenced by `@id` everywhere else.

## 7. AI module (Filament)

All AI output lands in `ai_drafts` and never edits a live field directly.

1. **Content assistant:** buttons in the page editor that draft meta title and description, FAQs, schema and internal link suggestions from the page copy.
2. **Page generator:** draft-only. Starts from a `venues`, `suburbs` or `vehicles` row, requires real facts (distance, drive time, pickup point) and blocks publishing when text is too similar to an existing page. Needs Shakawath's approval to publish.
3. **SEO audit:** a scheduled crawler checking titles, descriptions, H1s, canonicals, broken links, redirect chains, orphan pages, schema validity, thin pages and duplicate content. Results in a dashboard with severity.
4. **Monitor:** daily Search Console API pull into `gsc_metrics`. Flags pages whose impressions or clicks drop week on week. Sends an email digest. Also checks that every URL in the migration map still returns the expected status.
5. **Refresh queue:** for flagged or aging pages, produces a suggested update as a draft for review.

Needs: an Anthropic API key (and who pays for usage), and a Google service account added to the Search Console property.

## 8. Hosting on Hostinger Cloud

- Laravel 12 needs PHP 8.2 or higher. Confirm the plan offers it.
- Point the document root to `/public`. If hPanel will not allow it, place the app outside `public_html` and expose only `public`.
- Scheduler: one cron entry every minute running `php artisan schedule:run`, plus `queue:work --stop-when-empty` for the AI and audit jobs.
- Database queue and cache. No Redis.
- Deploy by Git pull over SSH, with `composer install --no-dev` and `php artisan optimize`.
- Build and test on a staging subdomain with noindex and HTTP authentication.

## 9. Build phases and cutover

1. **Foundation:** Laravel, Filament, models, importer, router, redirect manager, sitemaps, robots, Tailwind layout.
2. **Templates:** rebuild the 13 templates so they match the live design. Import all 140 URLs.
3. **Existing schema and tracking carried over verbatim:** the 12 JSON-LD blocks, GTM and GA4. Build the schema generator and FAQ components, but keep them switched off.
4. **AI module:** assistant, generator, audit, monitor.
5. **QA:** crawl staging against the live baseline and compare every URL: status, canonical, title, description, H1, heading outline, word count, internal links, schema. Zero unexplained differences on the protected tier.
6. **Cutover:** confirm conversion tracking end to end first (place a real test booking, cancel it afterwards, and check the event in GA4 DebugView, plus the quote-form thank-you event). Then freeze WordPress edits, final import, lower DNS TTL, switch DNS, submit sitemaps, keep WordPress on a hidden authenticated subdomain for 30 days as a fallback. Monitor Search Console daily for four weeks.
7. **Release 1.1 (2 to 4 weeks after cutover):** switch on the new schema, FAQ blocks, llms.txt and the answer-first summaries, in batches.
8. **Phase 2 (after rankings are stable):** fix long titles and descriptions, the /blog/ noindex, the duplicate-titled pages, and start the redesign.

## 10. Open items

1. (Done: content export, redirect list, uploads folder and form exports.)
2. (Done: Fluent Forms exports for forms 4, 7 and 9.) Still needed: the reCAPTCHA v3 site and secret keys (set in the server's .env by you, not shared in chat) and the SMTP sender details used by FluentSMTP. The `hidden_2` parameter and the Gmail copy are dropped.
2a. The "CS Corporate Landing Page" plugin renders /corporate-chauffeur/ with no content in the database. No file is needed from you: the importer will take the public rendered HTML of that page instead.
3. Decide what to do with the broken redirect to the draft /security-escorted-service/ page: publish the page, or point the rule at /corporate-hire/ or /vip/.
4. Confirm non-www is the canonical host and https is forced.
5. Check the Limo Anywhere admin for a post-booking redirect URL setting or a custom tracking-code field, and note what each is called and which plan feature it sits under. Also confirm whether Google Ads conversions are in use. (GTM and GA4 IDs are done. The conversion becomes a confirmed Limo Anywhere booking.)
6. Search Console access for the API (service account), and the standard Performance export with clicks.
7. Decisions: is /blog/ noindex intentional, and is /hire-private-jet/ a leftover?
8. Anthropic API key and billing owner.
9. Screenshots or a walkthrough of the key templates for the design rebuild.
