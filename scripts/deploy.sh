#!/usr/bin/env bash
# Run on the Hostinger server from the project root: bash scripts/deploy.sh
set -euo pipefail

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
if [ -f package.json ]; then npm ci && npm run build; fi
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart || true
echo "Deployed $(git rev-parse --short HEAD)"
