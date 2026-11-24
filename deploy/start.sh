#!/bin/sh
set -eu

mkdir -p "$APP_STORAGE_PATH/framework/cache/data" \
    "$APP_STORAGE_PATH/framework/sessions" \
    "$APP_STORAGE_PATH/framework/views" \
    "$APP_STORAGE_PATH/logs" "$APP_STORAGE_PATH/app/public"

# Migrations are explicit, never executed by a public web request or a cold start.
exec frankenphp run --config /etc/caddy/Caddyfile
