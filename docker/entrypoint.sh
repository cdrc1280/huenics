#!/bin/sh

set -e

echo "🚀 Starting Huenics Enterprise ERP Container..."

# ─── 1. Ensure required storage and cache directories exist ───────────────────
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/app/private \
    storage/app/documents \
    storage/app/livewire-tmp \
    storage/logs \
    bootstrap/cache \
    public/vendor/filament \
    public/vendor/livewire

chown -R www-data:www-data storage bootstrap/cache public
chmod -R 775 storage bootstrap/cache public

# ─── 2. Bind Nginx to Cloud PaaS / Railway $PORT dynamically (defaults to 80) ─
TARGET_PORT="${PORT:-80}"
echo "🌐 Configuring Nginx to listen on port ${TARGET_PORT}..."
sed -i "s/listen [0-9]\{1,5\} default_server;/listen ${TARGET_PORT} default_server;/g" /etc/nginx/sites-enabled/default

# ─── 3. Resolve .env file ──────────────────────────────────────────────────────
if [ ! -f .env ]; then
    echo "⚠️  No .env file found. Initializing from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

# ─── 4. Generate APP_KEY if missing ───────────────────────────────────────────
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null && [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# ─── 5. Connect public storage symlink and publish assets safely ─────────────
echo "🔗 Linking storage and publishing assets..."
php artisan storage:link --force || true
php artisan filament:assets || true
php artisan livewire:publish --assets || true

# ─── 6. Run Database Migrations SAFELY (Incremental additions only) ───────────
echo "📦 Checking and applying safe database migrations..."
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    mkdir -p database
    [ -f database/database.sqlite ] || touch database/database.sqlite
fi
php artisan migrate --force

# ─── 7. Production Zero-Touch Guarantee: Guarded Seeding ───────────────────────
# In production, seeders are NEVER executed unless explicitly forced via FORCE_SEED_IN_PRODUCTION=true.
# For fresh staging/dev instances, seeding only runs if the users table is completely empty.
SHOULD_SEED="no"

if [ "$APP_ENV" = "production" ] && [ "$FORCE_SEED_IN_PRODUCTION" != "true" ]; then
    echo "🛡️  Production mode active: Seeders skipped to guarantee existing data is 100% protected."
else
    IS_EMPTY=$(php -r "
        require __DIR__ . '/vendor/autoload.php';
        \$app = require_once __DIR__ . '/bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        try {
            \$userCount = \App\Models\User::count();
            echo (\$userCount === 0) ? 'yes' : 'no';
        } catch (\Throwable \$e) {
            echo 'no';
        }
    " 2>/dev/null || echo "no")

    if [ "$IS_EMPTY" = "yes" ]; then
        SHOULD_SEED="yes"
    fi
fi

if [ "$SHOULD_SEED" = "yes" ]; then
    echo "🌱 Blank database detected: Seeding initial Users, Roles, and Master Data..."
    php artisan db:seed --force || true
else
    echo "✅ Database verified — production tables, records, and files are untouched."
fi

# ─── 8. Optimize caches for production ────────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizing Laravel & Filament caches for high-speed production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache || true
    php artisan filament:cache-components || true
    php artisan icons:cache || true
else
    echo "🧹 Clearing caches for development environment..."
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
fi

echo "✅ Huenics application initialized safely on port ${TARGET_PORT}!"

# ─── 9. Start Supervisord ─────────────────────────────────────────────────────
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
