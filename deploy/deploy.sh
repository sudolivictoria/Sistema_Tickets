#!/usr/bin/env bash
# ============================================================================
# deploy.sh — rutina de despliegue a producción para Sistema de Tickets (ISTU)
#
# Uso en el servidor Ubuntu:
#   chmod +x deploy/deploy.sh
#   ./deploy/deploy.sh
#
# Requiere: git, PHP-FPM, Composer, Node/npm, Supervisor ya instalados y
# configurados (ver deploy/laravel-worker.conf). Corre como el usuario dueño
# del proyecto (NO como root) salvo los pasos de systemctl, que sí requieren
# sudo (ver comentarios abajo).
# ============================================================================
set -euo pipefail

# ----------------------- Configuración (ajustar) --------------------------
APP_DIR="/var/www/sistema-tickets"
BRANCH="main"
PHP_FPM_SERVICE="php8.2-fpm"     # ajustar a la versión de PHP real del servidor
HEALTH_URL="http://127.0.0.1/up" # ruta de health-check de Laravel 11 (bootstrap/app.php)
# ----------------------------------------------------------------------------

log() { echo -e "\n\033[1;34m==>\033[0m $1"; }
fail() { echo -e "\n\033[1;31mERROR:\033[0m $1"; exit 1; }

cd "$APP_DIR" || fail "No se pudo entrar a $APP_DIR"

log "1/10 Activando modo mantenimiento..."
php artisan down --retry=60 || true

log "2/10 Actualizando código (git pull origin $BRANCH)..."
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

log "3/10 Instalando dependencias PHP (composer, sin dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

log "4/10 Instalando dependencias JS y compilando assets..."
if [ -f package.json ]; then
    npm ci
    npm run build
fi

log "5/10 Aplicando migraciones de base de datos..."
php artisan migrate --force

log "6/10 Limpiando cachés previos (config, rutas, vistas, eventos)..."
php artisan optimize:clear

log "7/10 Generando cachés de producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

log "8/10 Reiniciando PHP-FPM (requiere sudo)..."
sudo systemctl restart "$PHP_FPM_SERVICE" || fail "No se pudo reiniciar $PHP_FPM_SERVICE"

log "9/10 Reiniciando workers de cola y Reverb (Supervisor)..."
sudo supervisorctl restart laravel-worker:* || fail "No se pudo reiniciar laravel-worker"
sudo supervisorctl restart laravel-reverb:* || echo "Aviso: laravel-reverb no configurado en Supervisor, omitido."

log "10/10 Desactivando modo mantenimiento..."
php artisan up

log "Verificando salud de la aplicación ($HEALTH_URL)..."
if curl -fsS -o /dev/null "$HEALTH_URL"; then
    log "Despliegue completado correctamente."
else
    fail "El health-check falló tras el despliegue. Revisar storage/logs/laravel.log de inmediato."
fi
