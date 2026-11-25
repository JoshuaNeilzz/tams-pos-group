# TAMS POS - CipherStack

## Live website

**[Open TAMS POS](https://tams-pos-group.vercel.app)**. No XAMPP or installation is needed to open the hosted site.

**Team:** Joshua Neil Biglete, Leah Buenaventura, Gabrielle Crisostomo, and Kurt Airry Wenceslao.

TAMS POS is an IT0049 Web System Technologies classroom project: a **sari-sari store selling Philippine packaged snacks**, built with Laravel 12, Blade, PHP, and a relational database. Nova, Piattos, V-Cut, Chippy, Oishi, and Boy Bawang are the illustrative sample lineup. Staff can add other foods/snacks. All sample prices are classroom values, not advertised manufacturer prices. Product names belong to their respective brands; the packet sketches are original SVG illustrations, not official product photos or packaging.

The checked-in repository documentation previously described a generic inventory system and imposed no product-category restriction. The original assignment brief is not included here, so category restrictions in that external document cannot be independently confirmed from this repository.

## Which account should I use?

**Hosted access verified on November 26, 2026.** Both accounts below were provisioned in the hosted database and tested on the live site. Buying/reserving, restocking, cancellation, pickup completion, counter sales, and purchase history in a fresh browser session were verified. The earlier live site did not contain the local demo accounts; that mismatch is now fixed.

| Account | Sign-in page | Username | Password | Purpose |
| --- | --- | --- | --- | --- |
| Evaluation staff | [Staff sign in](https://tams-pos-group.vercel.app/staff/login) | `demo_staff` | `DemoStaff123!` | Add snacks and stock, record counter sales, complete pickup orders, manage customers/staff |
| Evaluation shopper | [User sign in](https://tams-pos-group.vercel.app/login) | `demo_user` | `DemoUser123!` | Buy/reserve snacks, cancel own pending orders, view own completed purchases |

These are **shared classroom evaluation credentials**. Both work on the hosted site linked above and on a local copy after the explicit setup below. Use fictional data only. Shared staff access can modify the evaluation store; it must never be enabled on a store holding real customer information or real business data.

You can also [register your own shopper account](https://tams-pos-group.vercel.app/register). A [new staff registration](https://tams-pos-group.vercel.app/staff/register) needs approval by an existing active staff member; it does not immediately grant stock-management access.

## Buy snacks (shopper)

1. Sign in through **User sign in**, then open **My account**.
2. Go to **Product catalog**, choose the number of packs, and select **Buy / reserve**.
3. The order appears in **My pickup orders**. Available stock decreases immediately to reserve your packs. The server uses the stored price, not a price submitted by the browser.
4. Pay **cash at the store when collecting**. This app does not collect card details or process online payments. A reservation is not a paid sale.
5. Staff confirms **Paid & collected**. The order then appears in Purchase history and Sales history. Stock is not deducted a second time.
6. Before completion, you can cancel your own order. Reserved packs are returned once, even if the cancel button is submitted twice.

The account catalog is no longer view-only. A shopper cannot restock, manage staff, or access someone else's orders.

## Add stock and sell (approved staff)

1. Sign in through **Staff sign in**. Open **Snacks & stock**.
2. Use **Add snack** for a new product. Enter the selling price per pack and initial available quantity. A photo upload is optional; a packet sketch is used when there is no photo.
3. For a delivery, expand **+ Add stock** on the relevant snack card. Enter the **new packs received**, not the final total. An optional delivery note is saved with the staff member, quantity received, and resulting available balance in `stock_movements`.
4. Use **Edit** for a price change or stock-count correction. A stale form is rejected if stock changed while the page was open; reload before correcting stock.
5. For a walk-in purchase, use **Sales > Record sale** or the card's **Sell** link. Choose a snack, optional customer, and quantity. Confirm after collecting payment. Total and stock update together. Selecting a registered shopper's linked customer makes the counter sale visible in their Purchase history.
6. For an existing pickup order, use **Orders > Paid & collected**, not Record sale. This avoids creating a second sale. Cancel a pending no-show order to release its reserved stock.

All stock figures mean **available packs**, excluding pending pickup reservations. Completed sales, historical names, and receipts survive later product archiving. Stock and totals are validated server-side. Overselling, negative quantities, excessive totals, and repeat checkout submissions are rejected or safely reused without double deduction.

## Sample snack catalog

| Snack | Sample price / pack | Starting packs |
| --- | --- | --- |
| Nova Multigrain Chips | PHP 18.00 | 30 |
| Piattos Cheese | PHP 22.00 | 30 |
| V-Cut Potato Chips | PHP 25.00 | 24 |
| Chippy Barbecue | PHP 12.00 | 40 |
| Oishi Prawn Crackers | PHP 10.00 | 40 |
| Boy Bawang Cornick | PHP 15.00 | 36 |

Setup adds missing sample products; rerunning it does not reset prices, replenish stock, restore archived snacks, or erase transactions. Packet art is in `public/images/snacks/`; the editable drawing source is `scripts/draw-snacks.cjs` (run `node scripts/draw-snacks.cjs` to redraw it). It uses editable SVG paths, hatching, and lettering; no image-generation service is used. Uploaded product photos take precedence over the sketches.

## Local development setup

`localhost` / `127.0.0.1` refer to your own computer, not the hosted site. These steps are only for running a separate local copy.

Requirements: PHP 8.2+ with PDO MySQL (or PDO SQLite for evaluation), GD/WebP, fileinfo, mbstring, XML, and Laravel's standard extensions; Composer; and MySQL/MariaDB when using XAMPP. No Node build is required for the committed CSS, native JavaScript, and system fonts.

```sh
composer install
cp .env.example .env
php artisan key:generate
```

In PowerShell, use `Copy-Item .env.example .env`. Create an empty MySQL database named `tams_market` and set the database values in your local `.env`. Start MySQL in XAMPP.

```sh
php artisan migrate --force
php artisan pos:prepare-evaluation
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000** for your local copy. The evaluation account table above now applies to this local database. `php artisan db:seed --class=DemoSeeder` is an alternative local-only sample setup. The default `DatabaseSeeder` still creates a first private approved staff account with a generated password when no approved staff exists; it is separate from the named demo accounts.

For an existing local database whose demo passwords were changed, explicitly run:

```sh
php artisan pos:prepare-evaluation --reset-passwords --archive-stationery
```

This resets only `demo_staff` and `demo_user`, invalidates their existing sessions, adds missing snacks, and soft-archives the three former sample stationery products (Notebook, Gel Pen, Desk Organizer). It preserves other accounts, sales, and existing snack prices/quantities. Omit the optional flags to preserve passwords and old sample products.

## Hosted evaluation setup (maintainer only)

See [deploy/README.md](deploy/README.md) for the Vercel PHP container and persistent database setup. Never commit production database credentials, application keys, or downloaded environment files.

1. Use the existing Vercel project `tams-pos-group` and its dedicated hosted database.
2. Run `php artisan migrate --force` against that hosted database from an authorized maintenance environment. No fresh/reset migration is needed. The new migration adds `orders` and `stock_movements` without deleting existing data.
3. For this **classroom demonstration only**, set `POS_EVALUATION_MODE=true` in production. Then explicitly run `php artisan pos:prepare-evaluation --reset-passwords --archive-stationery` against the hosted database. The command refuses shared accounts in production unless this flag is enabled. It does not run during normal page requests or cold starts.
4. Deploy the revision, test both named accounts through their correct sign-in forms, and verify restock -> customer reservation -> staff completion -> purchase history. Update the hosted setup status above only after those checks pass.
5. Keep shared evaluation data fictional. For real deployment, disable evaluation mode and remove/disable the shared accounts; create private staff credentials instead.

## Account permissions

| Capability | Guest | Shopper | Pending staff | Approved staff |
| --- | --- | --- | --- | --- |
| Read Home / How it works | Yes | Yes | Yes | Yes |
| Register | Yes | Sign out first | Sign out first | Sign out first |
| View own account | No | Yes | Yes | Yes |
| Buy/reserve snacks | No | Yes | No | No (use counter sales) |
| Cancel own pending order | No | Yes | No | Any pending order |
| View own purchase history | No | Yes | No | All sales through staff module |
| Add/edit/archive snacks and restock | No | No | No | Yes |
| View/complete all pickup orders | No | No | No | Yes |
| Manage customers/staff and approve requests | No | No | No | Yes |
| Record counter sales / see all sales | No | No | No | Yes |

User registration creates its own linked customer record rather than claiming a record by email. Purchase history is scoped to that stored customer ID; pickup orders are scoped to the authenticated user ID. Request-supplied user, customer, price, staff, and status values cannot override these rules. Approved staff share the management permissions; there is no separate administrator role. A staff member cannot remove their own signed-in account.

## Routes and database

- `/`, `/about`: public overview, snack lineup, workflow, and CipherStack team.
- `/login`, `/register`: shopper access. `/staff/login`, `/staff/register`: staff access/request.
- `/account`: shopper catalog, own pickup orders, own purchases, or pending staff status.
- `/dashboard`, `/products`, `/customers`, `/users`, `/sales`: approved-staff management.
- `POST /products/{product}/restock`: approved-staff stock delivery.
- `POST /orders`: active shopper reserves packs; UUID request key prevents duplicate checkout.
- `GET /orders`, `PATCH /orders/{order}/complete`: approved-staff order management.
- `PATCH /orders/{order}/cancel`: the owning shopper or approved staff only.

Core tables are `users`, `customers`, `products`, and `sales`. New `orders` stores the reservation, locked unit price/total, customer/product snapshots, status, and linked completed sale; `stock_movements` records deliveries. Product/staff soft deletion preserves references. Customer deletion clears optional references but retains snapshot names; edit registered customers instead of deleting them when portal history must be retained. MySQL/MariaDB and hosted PostgreSQL support row-level transaction locks. SQLite supports local testing.

Only JPG/PNG/WebP uploads up to 2 MB and 6000 x 6000 pixels are accepted. Uploaded product images become 640 x 640 WebP; avatars become 256 x 256 WebP. The host stores prepared uploads in the database. Local installs default to file storage. Uploaded media is authenticated. The bundled SVG sketches are public assets and are not accepted as uploads.

The original cache/job and legacy `login_accounts` tables remain for compatibility; the latter is not used to sign in. Authentication includes CSRF protection, login throttling, regenerated sessions, hashed passwords, and role checks. Dates use Asia/Manila; amounts use Philippine pesos.

## Verification

```sh
php artisan test
```

The regression suite passes **36 tests (339 assertions)**. Coverage includes account separation/approval, customer and staff CRUD, prepared images and hosted media, server-priced counter sales, stock limits, checkout retries, reservation/cancellation/completion, order ownership, restock receipts, stale edit protection, and guarded evaluation setup. Before submitting the hosted URL, also perform the real-browser scenario described above against the hosted database; passing local tests alone does not provision live accounts.

The project retains Laravel rather than the assignment's named CodeIgniter framework, based on the owner's earlier confirmation that Laravel is permitted. The repository follows Laravel MVC conventions. Group collaboration instructions are in [CONTRIBUTING.md](CONTRIBUTING.md).
