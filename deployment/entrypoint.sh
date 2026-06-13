#!/usr/bin/env sh
# Container boot sequence for the portfolio + hosting engine.
set -e
cd /var/www/html

echo "[entrypoint] preparing application…"
php artisan storage:link 2>/dev/null || true
php artisan migrate --force || true

# Production caches (safe to fail on first boot before env is fully set).
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

# Rebuild every hosted project's nginx snippet from the database. The hosted
# storage dir is ephemeral on Railway, so this repopulates it each start.
echo "[entrypoint] regenerating hosted nginx snippets…"
php artisan hosting:regenerate-nginx

# Render the nginx config with the platform-provided PORT.
export PORT="${PORT:-8080}"
export HOSTING_MAX_UPLOAD_MB="${HOSTING_MAX_UPLOAD_MB:-500}"
envsubst '${PORT} ${HOSTING_MAX_UPLOAD_MB}' \
    < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
echo "[entrypoint] nginx listening on ${PORT}"

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
