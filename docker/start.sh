#!/usr/bin/env sh
set -eu

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force

# Perintah Artisan (terutama cache permission dari Spatie) berjalan sebagai root
# ketika container dimulai. Kembalikan ownership setelah seluruh proses selesai
# supaya PHP-FPM yang berjalan sebagai www-data dapat memperbarui cache runtime.
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
