# TAMS POS

IT0049 Web System Technologies group project. TAMS POS manages products, customers, staff, and sales using Laravel 12, Blade, PHP, and MySQL/MariaDB.

The application has separate user and staff registration/sign-in screens. The public home page explains both account types, and the **How it works** page describes the workflow for an evaluator.

## Quick start for the evaluator

Requirements: PHP 8.2+ with PDO MySQL, GD with WebP, fileinfo, mbstring, XML, and Laravel's standard extensions; Composer; and MySQL/MariaDB. XAMPP provides PHP, Apache, and MariaDB on Windows.

From a fresh clone:

```sh
composer install
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp` if preferred. Create an empty MySQL database named `tams_market`, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to match your installation. Start MySQL in XAMPP.

```sh
php artisan migrate --force
php artisan db:seed
php artisan db:seed --class=DemoSeeder
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000**. No Node build is required: the interface uses the committed `public/css/app.css`, native JavaScript, and system fonts.

`DatabaseSeeder` creates the first approved staff account and prints its generated password. It preserves existing approved staff accounts. `DemoSeeder` adds the optional evaluation accounts and sample products below; it does not reset existing passwords or stock. It refuses to run in production.

### Local evaluation accounts

| Account | Sign-in page | Username | Password | Access |
| --- | --- | --- | --- | --- |
| Demo staff | `/staff/login` | `demo_staff` | `DemoStaff123!` | Products, customers, staff, sales, dashboard |
| Demo user | `/login` | `demo_user` | `DemoUser123!` | Product catalog and own purchase history |

These credentials are only for the optional local demo dataset. They are not production credentials. The generic demo products are Notebook (PHP 85.00, 20 units), Gel Pen (PHP 25.50, 40 units), and Desk Organizer (PHP 199.00, 5 units).

## Suggested demonstration

1. Open Home. Use the **User account** and **Staff account** sections to see the distinct registration and sign-in links.
2. Sign in as `demo_staff` through **Staff sign in**. Review Dashboard, Products, Customers, Staff, and Sales.
3. In Products, add a product, upload a JPG/PNG/WebP image, edit its price or stock, and archive an unused product. Uploaded product images are prepared as 640 × 640 WebP images.
4. In Customers, add, view, edit, and delete a sample customer. The **Demo User** customer is linked to the demo user's account.
5. In Sales, choose **Record sale**. Select Gel Pen, choose Demo User as the customer, and enter 2 units. On a fresh demo dataset the total is PHP 51.00 and stock changes from 40 to 38.
6. Attempt to sell more units than are available. The form and server reject it, and stock and history stay unchanged. Also try a walk-in sale without selecting a customer.
7. Review Sales history. It shows product, customer or walk-in, staff, quantity, total, and date.
8. Sign out. Sign in as `demo_user` through **User sign in**. My account shows the sale linked to Demo User and the product catalog. Opening `/dashboard`, `/products`, `/customers`, `/users`, or `/sales` as this user returns 403.
9. Sign out and select **Create user account**. Registration creates a user account and a new linked customer record. It immediately opens My account. Record future sales against that linked customer to populate the user's history.
10. Sign out and select **Create staff account**. Register with a unique username and email, confirmed password, and optional avatar. The new account shows **Staff approval pending** and has no management access.
11. Sign out, sign in as `demo_staff`, open Staff, and select **Approve** on the pending registration. The approved staff account can now sign in through Staff sign in and use the management modules.
12. In Staff, demonstrate adding a staff member directly, viewing/editing the profile, changing a password, uploading an avatar, and removing another staff member. Avatars are prepared as 256 × 256 WebP images. A staff member cannot remove their own signed-in account.

## Account types and permissions

| Capability | Guest | User | Pending staff | Approved staff |
| --- | --- | --- | --- | --- |
| Read Home and How it works | Yes | Yes | Yes | Yes |
| Register user or staff account | Yes | Sign out first | Sign out first | Sign out first |
| View own account | No | Yes | Yes | Yes |
| Browse account product catalog | No | Yes | No | Staff product module |
| View own purchases | No | Yes | No | Staff sales module |
| Manage products/customers/staff | No | No | No | Yes |
| Approve staff registration | No | No | No | Yes |
| Record sales and view all history | No | No | No | Yes |

A user is a customer-facing login. A customer is a contact record used by sales; walk-in/directory-only customers do not need a login. User registration creates its own linked customer record rather than claiming an existing record by email. Purchase access uses the stored customer ID and cannot be changed through query parameters.

Public staff registration always creates a **pending** account. Existing approved staff must approve it. The request cannot choose its own role, approval status, or customer link. Staff accounts created directly inside Staff are immediately active. On a fresh installation, `php artisan db:seed` provides the initial approved staff account so the group can approve subsequent requests.

All approved staff have the same management permissions required by the assignment; this project does not define an additional administrator role.

## Assignment coverage

| Requirement | Implementation |
| --- | --- |
| Product CRUD or archive, prepared image | Products: create/list/edit/archive; image validation and 640 × 640 WebP preparation |
| Customer CRUD | Customers: create/list/view/edit/delete |
| Staff CRUD, avatar, hashed password | Staff: create/list/view/edit/remove; 256 × 256 avatar preparation; Laravel hashed password cast |
| Authenticated staff management | `auth` and `staff` middleware on every management route |
| Record sale and update inventory | Server-calculated total; product row lock, sale creation, and stock decrement in one database transaction |
| Reject quantity above available stock | Explicit validation message; no sale or stock mutation on rejection |
| Sales history | Product/customer/staff name snapshots, quantity, total, and timestamp |
| Reproducible database and setup | Committed migrations, initial staff seeder, optional generic demo seeder, and this README |

The assignment document names CodeIgniter. This project retains Laravel with the project owner's confirmation that Laravel is permitted. It follows Laravel MVC conventions.

## Routes

| Route | Purpose |
| --- | --- |
| `/` | Account choices and project overview |
| `/about` | In-application system guide |
| `/register` | User registration |
| `/login` | User sign-in form |
| `/staff/register` | Staff registration request |
| `/staff/login` | Staff sign-in form |
| `/account` | Own profile, user catalog/history, or pending-staff status |
| `/dashboard` | Approved staff overview |
| `/products` | Staff inventory management |
| `/customers` | Staff customer management |
| `/users` | Staff management and approval |
| `/sales/create` | Record sale |
| `/sales` | All sales history |

Both login forms submit to the same throttled authentication endpoint with their account type. Successful user and pending-staff sign-ins go to `/account`; approved staff sign-ins go to `/dashboard` or a previously requested staff page.

## Database and integrity

- `users`: username, full name, optional unique email, hashed password, avatar, role, status, optional customer reference, created date, remember token, and soft-delete date.
- `customers`: full name, email, phone, and created date. User accounts link to their customer record through `users.customer_id`.
- `products`: name, DECIMAL(10,2) price, unsigned stock quantity, optional image, created date, and soft-delete date.
- `sales`: product, optional customer, selling staff, quantity, DECIMAL(10,2) total, created date, and historical product/customer/staff name snapshots.

Product stock is checked with `lockForUpdate()` inside a database transaction. Sale creation and stock decrement succeed together. The server ignores submitted price/staff fields and calculates the persisted amount in integer cents from the current product price and authenticated staff ID. MySQL/MariaDB supplies the production row locks.

Products and staff are archived through soft deletion, preserving referenced sales. Deleting a customer clears the foreign keys while retaining its name on past sales. This also disconnects that customer's user account from past purchases; staff should edit a registered customer's contact record rather than delete it when retaining portal history is needed. Sales cannot be edited or deleted through the interface.

The original Laravel cache/job tables and legacy `login_accounts` table remain for compatibility. `login_accounts` is no longer used for authentication. Migrations reproduce the schema; a separate SQL dump containing personal records is not required for submission.

## Uploads and authentication

Only JPG, PNG, and WebP uploads up to 2 MB and 6000 × 6000 pixels are accepted. GD decodes and re-encodes them using random UUID filenames. Replaced images are removed after saving the new path. Files are stored under `storage/app/public/products` or `storage/app/public/avatars` and served through the authenticated `/media/{folder}/{file}` route. This works on Windows without a symlink and supports existing legacy files in `public/storage`.

The application uses CSRF protection, login throttling, session regeneration, password hashing, role checks, server-side validation, and scoped user purchase queries. Every management endpoint requires an active staff account, including write and approval actions. Dates use Asia/Manila and amounts use Philippine pesos.

## Upgrading an existing installation

Back up the database, keep the existing `.env` and `APP_KEY`, then run:

```sh
composer install
php artisan migrate --force
php artisan view:clear
```

The migrations preserve existing records. The first POS migration copied existing legacy email/password credentials into staff records. The account-role migration marks existing accounts as approved staff; new public user registrations receive the user role, and new public staff registrations remain pending. Existing passwords are not reset.

Backups on the development machine are stored privately in `storage/app/backups` and excluded by the storage ignore rules. Do not add personal database exports, real uploaded avatars, or `.env` to GitHub.

## Tests

```sh
php artisan test
php artisan view:cache
php artisan route:list --except-vendor
```

Tests use an isolated SQLite database and cover user/staff registration, role-forgery rejection, staff approval, customer history isolation, account-type sign-in, protected pages, CRUD, image preparation, password hashing, sale pricing, walk-ins, inventory updates, overselling rejection, and historical record preservation. Database migrations and local sale operations have also been checked against MariaDB. SQLite tests do not claim to reproduce MySQL row-lock concurrency behavior.

## GitHub and deployment

Commit application code, public CSS, migrations, seeders, tests, `composer.json`, `composer.lock`, `.env.example`, and this README. Exclude `.env`, vendor, node_modules, runtime caches, backups, and real uploaded personal files. Generic optional demo credentials are intentionally documented for local evaluation.

Use a PHP host with MySQL/MariaDB, Composer, HTTPS, and GD/WebP support. The web document root must point to `public`; `storage` and `bootstrap/cache` need write access. Run `composer install --no-dev --optimize-autoloader`, configure `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY`, database access, and secure cookies, then run migrations, the primary initial-account seeder, `php artisan config:cache`, and `php artisan view:cache`. Do not run DemoSeeder on a public production deployment.

For XAMPP development, `http://localhost/final-pos/public/` reaches this project's entry point. A virtual host pointing to `public` is preferable to exposing the repository root.

Group repository: https://github.com/JoshuaNeilzz/tams-pos-group. See [CONTRIBUTING.md](CONTRIBUTING.md) for joining the repository, creating branches, submitting pull requests, and keeping local databases in sync through migrations. A live PHP/MySQL deployment is separate; submit its URL alongside the repository URL when hosting is complete.

## Visual assets

The home and sign-in pages use `public/images/checkout-hero.png`, a project-local retail photograph created using the built-in Imagegen tool. It is decorative and does not show live inventory or actual store data. The image is committed with the source; no external image service is required. The interface retains its light/dark switch with a muted yellow accent in both themes.

<details>
<summary>Image generation prompt</summary>

Create a single editorial product photograph for the hero of a student point-of-sale and inventory web application. Landscape 4:3 composition. A thoughtfully arranged small stationery store checkout counter: compact off-white point-of-sale payment terminal with a dark unmarked screen, kraft paper shopping bag with simple folded handles, a warm ivory spiral notebook, one black pen, small stacks of cream and muted sage stationery boxes, a paper receipt showing ONLY indistinct gray lines (no readable text). Warm muted butter yellow background and surface, slightly different tones separated by a soft horizon, natural window light from upper left and realistic soft shadows. Camera three-quarter angle, close but breathable composition. Tactile real paper, matte plastics. Minimal attractive commercial still-life photography, grounded and plausible, mature editorial art direction. No people, no hands, no brands, no logos, no visible words, no typography, no decorative floating objects, no interface mockup, no gradients added graphically. The terminal and shopping bag are recognizable focal subjects. Rich warm restrained colors, image should also look attractive beside a dark charcoal website. Save a finished raster image suitable for a website.

</details>
