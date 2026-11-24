# Working on TAMS POS as a group

Repository: https://github.com/JoshuaNeilzz/tams-pos-group

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
- Use the generic demo data for demonstrations. Do not add real customer information or credentials to seeders.
- The account flow and demo logins are documented in README.md. User accounts must remain separate from approved staff accounts.
- Update the README when setup, permissions, or a demonstrated feature changes.

The repository contains a PHP application. Sharing its GitHub URL shares the source; running a live website requires a separate PHP/MySQL deployment.
