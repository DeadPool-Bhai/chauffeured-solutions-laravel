# Staging deploy on Hostinger

Staging URL used below: `staging.chauffeuredsolutions.com.au`. Swap in whatever subdomain you create.

## 1. Create the site (hPanel)
1. Domains, Subdomains: create `staging`. Note the folder it creates (for example `~/domains/chauffeuredsolutions.com.au/public_html/staging`).
2. Advanced, PHP Configuration: PHP 8.3 (required). Enable extensions: `mbstring`, `xml`, `intl`, `pdo_mysql`, `zip`, `gd`, `fileinfo`.
3. Databases, MySQL: create a database and user. Keep the three values for `.env`.
4. Email: create a mailbox to send from (for example `bookings@chauffeuredsolutions.com.au`). Check SPF and DKIM are on for the domain, or lead emails land in spam.
5. SSL: switch on the free SSL for the subdomain.
6. Advanced, SSH Access: enable it and note the host, port and user. Add your public key.

## 2. Get the code on the server (SSH)
```bash
cd ~/domains/chauffeuredsolutions.com.au
git clone -b develop https://github.com/DeadPool-Bhai/chauffeured-solutions-laravel.git staging-app
cd staging-app
cp .env.staging.example .env      # then edit .env (see section 3)
composer install --no-dev -o
npm ci && npm run build           # if Node is missing on the plan, run the build locally and upload public/build
php artisan key:generate
php artisan migrate --force
```
The repo is private, so use a GitHub deploy key or a fine-grained token with read access.

## 3. Point the subdomain at `public`
Easiest: in hPanel, Subdomains, set the custom folder to `.../staging-app/public`. If the plan does not allow that, replace the subdomain folder with a symlink:
```bash
rm -rf ~/domains/chauffeuredsolutions.com.au/public_html/staging
ln -s ~/domains/chauffeuredsolutions.com.au/staging-app/public ~/domains/chauffeuredsolutions.com.au/public_html/staging
```

## 4. `.env` values you fill in
Copy from `.env.staging.example`. You set these yourself, they do not go in chat or in git:
- `APP_URL`, `STAGING_AUTH` (user:password for the HTTP login), `DB_*`
- `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
- `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET`: the v3 key must list the staging domain. Leave both blank to skip the check on staging.
- `APP_STAGING=true` gives: `X-Robots-Tag: noindex, nofollow` on every response, `robots.txt` disallowing everything, no GTM or GA4, and the HTTP login. Remove it (set false) at cutover.

## 5. Content and media
```bash
php artisan import:wordpress /path/to/export.xml --snapshot=database/seeders/data/seo_snapshot.json
php artisan db:seed --force
php artisan make:filament-user       # first admin, then log in at /admin
```
Media: upload the uploads zip, then copy the year folders only (not `backup/`, `elementor/`, `template-kits/`, plugin folders) into `public/wp-content/uploads/`. Check one image URL loads, for example `/wp-content/uploads/2025/10/wedding-chauffeur-service-.jpg`.

## 6. Cron (hPanel, Advanced, Cron Jobs), every minute
```
* * * * * cd ~/domains/chauffeuredsolutions.com.au/staging-app && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd ~/domains/chauffeuredsolutions.com.au/staging-app && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```
The second line sends lead emails. Without it leads are saved but nobody is notified.

## 7. Smoke test
1. Open the site: browser asks for the HTTP login, then the home page loads with styles and images.
2. View source: `<meta name="robots">` and the `X-Robots-Tag` header say noindex. `/robots.txt` says `Disallow: /`.
3. Submit the quote form on `/people-mover/`. You land on `/thank-you-f4-quote-request/`, the lead appears in `/admin/leads`, and the email arrives within a minute.
4. Submit the wedding quote on `/wedding-chauffeur/` and the booking form on `/wedding-chauffeur-booking-form/`.
5. `/booking/` shows the Limo Anywhere embed.

## 8. QA crawl against live
Run on the server (it can reach both sites):
```bash
php artisan qa:crawl https://staging.chauffeuredsolutions.com.au --user=qa --pass='PASSWORD' --redirects
```
- Compares every published URL with live: status, canonical, title, meta description, robots, H1, JSON-LD (hard), plus H2/H3 counts and word count (soft).
- Prints the pages with hard differences and writes every difference to `storage/app/qa-crawl.csv`.
- Zero hard differences is the target for the protected top-10 pages. Expect soft differences on the home page (trimmed body) and where accordions became H3.
- `--only=/service-area/` limits the run. `--limit=10` is a quick trial.
- `--redirects` lists redirect rules that behave differently from live. Flattened chains are expected to differ.

## 9. Later: updating staging
`bash scripts/deploy.sh` pulls, installs, builds, migrates and rebuilds caches.
