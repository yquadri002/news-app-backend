#!/bin/sh
set -e

if [ -f artisan ]; then
    php artisan config:cache --no-ansi 2>/dev/null || true
    php artisan route:cache --no-ansi 2>/dev/null || true
    php artisan view:cache --no-ansi 2>/dev/null || true
fi

exec "$@"
