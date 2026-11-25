# Working on TAMS POS as a group

Repository: https://github.com/JoshuaNeilzz/tams-pos-group

## Use the live store before editing code

Live site: **https://tams-pos-group.vercel.app**. No Vercel login, GitHub login, XAMPP, or installation is needed to use it. The README has the full [sign-in guide](README.md#sign-in-to-the-live-website-start-here), [add-snack example](README.md#worked-example-add-a-new-snack), and [restock example](README.md#worked-example-add-stock-to-an-existing-snack).

| Role | Sign-in link | Username | Password |
| --- | --- | --- | --- |
| Approved evaluation staff | https://tams-pos-group.vercel.app/staff/login | `demo_staff` | `DemoStaff123!` |
| Evaluation shopper | https://tams-pos-group.vercel.app/login | `demo_user` | `DemoUser123!` |

Use staff access to add snacks or stock. Use shopper access to buy/reserve and view your own orders. Sign out before switching roles, or use separate browser profiles. Newly self-registered staff need approval; the evaluation staff account is already active.

The hosted store is shared: stock changes, orders, staff approvals, and edits affect the whole group. Use fictional data. Do not change the shared demo credentials during a group presentation. Add a personal account if you want a separate login. Adding snacks or restocking is done inside the site and does not require Git commits or a new deployment.
## Join the repository

The repository owner must invite each groupmate using their GitHub username under **Settings → Collaborators → Add people**. Accept the GitHub invitation before cloning this private repository. Each member should use their own GitHub account.

## Set up your own copy

Install Git, PHP 8.2+, Composer, and MySQL/MariaDB, then run:

```sh
git clone https://github.com/JoshuaNeilzz/tams-pos-group.git
cd tams-pos-group
composer install
```

Copy `.env.example` to `.env`, create a local `tams_market` database, and update the database credentials in `.env`. Follow the README quick start for key generation, migrations, demo accounts, and running the application. Each member has a separate local database; Git does not synchronize database records.

## Make and submit a change

Start each task from the latest main branch and use a short branch name describing your work:

```sh
git switch main
git pull --ff-only origin main
git switch -c feature/product-search
```

Edit the files, check the affected pages in light and dark mode, and run:

```sh
php artisan test
git status
git add path/to/changed-file
git commit -m "Describe the change"
git push -u origin feature/product-search
```

Open a pull request on GitHub from your branch into `main`. Explain what changed and how you tested it. Ask a groupmate to review it, merge it when ready, and pull the updated main branch before starting the next task.

After pulling database changes, run `php artisan migrate`. Create new migrations for schema changes rather than editing migrations other members have already applied.

## Files and data

- Commit application code, tests, migrations, seeders, shared design assets, and documentation.
- Keep `.env`, application keys, database passwords, database backups, dependencies, runtime files, and personal uploads out of commits. The ignore rules already cover these locations.
- Use the fictional snack-store evaluation data for demonstrations. Do not add real customer information or credentials to seeders.
- The account flow and demo logins are documented in README.md. User accounts must remain separate from approved staff accounts.
- Update the README when setup, permissions, or a demonstrated feature changes.

The repository contains the PHP application source. The running version is hosted at https://tams-pos-group.vercel.app. A Git push updates GitHub; verify a successful production deployment before assuming code changes are live. Local database changes are never synchronized automatically to the hosted database.
