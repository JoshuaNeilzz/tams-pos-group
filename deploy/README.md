# Hosting TAMS POS on Vercel

The root Dockerfile uses Vercel's Container preset and FrankenPHP. `public` is the only web root. The image installs production Composer dependencies, GD/WebP, and both MySQL and PostgreSQL PDO drivers. No local `.env`, backup, upload, or deployment credential is included in the container context.

The Vercel PHP container guide is https://vercel.com/kb/guide/deploy-php-on-vercel-with-docker. Container availability and provider quotas are controlled by Vercel and the connected database provider.

## Persistent data

Attach a hosted relational database, such as a PostgreSQL database through Vercel Marketplace, or a hosted MySQL service. Local XAMPP cannot serve as the database for a website that must work while the development PC is off.

The relationships and migrations work with MySQL/MariaDB locally and PostgreSQL when deployed. For PostgreSQL, set `DB_CONNECTION=pgsql` and `DATABASE_URL` to the provider's TLS-enabled connection URL; `DB_URL` takes precedence when explicitly set. For MySQL, set `DB_CONNECTION=mysql` with the normal `DB_*` values and the provider's verified TLS CA configuration.

The container defaults to database sessions and cache. Prepared product images and avatars are stored as base64 WebP data in `uploaded_images`, served through the authenticated media route. This is intended for this small educational application and avoids losing uploads across serverless instances. At larger scale, migrate media to object storage. A local installation still defaults to its existing file-based upload storage.

Only compiled views and other disposable runtime files use `/tmp/tams-storage`. No persistent database, upload, or session should be stored in `/tmp`.

## Production configuration

Set these values in Vercel's environment-variable settings, never in Git:

| Variable | Value |
| --- | --- |
| `APP_NAME` | `TAMS POS` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | A newly generated application key, retained across redeployments |
| `APP_URL` | The assigned `https://...vercel.app` production URL |
| `DB_CONNECTION` | `pgsql` or `mysql`, matching the hosted database |
| `DATABASE_URL` | PostgreSQL TLS connection URL when using PostgreSQL |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `MEDIA_STORAGE_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |

Provision a dedicated database for production. Preview deployments must use a separate database when testing schema changes or destructive data operations. Do not share the development database or local `.env` with Vercel.

## Deployment sequence

1. Import `JoshuaNeilzz/tams-pos-group` or link it using Vercel CLI. Confirm the framework is **Container**, the root directory is the repository root, and Dockerfile detection is enabled.
2. Attach the hosted database and set the environment variables above. Database-provider authorization or billing/terms steps must be completed by the account owner when requested.
3. Apply migrations to that hosted database explicitly using a secure maintenance environment with its connection settings: `php artisan migrate --force`. Do not run `migrate:fresh` on an existing production database.
4. On a new hosted database, run the primary seeder with a private `POS_ADMIN_PASSWORD` to create the first approved staff member. Do not run DemoSeeder in production. Keep the initial staff credentials private and share them only with the group.
5. Deploy the production build. Migrations and seeders are deliberately not run on web requests or container cold starts.
6. Verify the production URL's home page, sign-in, registration, management permissions, media uploads, sale recording, and overselling rejection. Confirm that a second browser session can retrieve uploaded images and that data remains after a redeploy.

The `/up` Laravel endpoint checks application bootstrap; it does not by itself demonstrate working authentication, persistent uploads, or a healthy remote database.

If the database provider supplies separate pooled and direct URLs, use the direct connection for migrations and its supported pooled connection for web traffic. Never print or commit either URL.
