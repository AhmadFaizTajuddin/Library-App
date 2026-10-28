| Ahmad Faiz Tajuddin | 5025231291 | 

# Campus Library: Laravel CRUD project

A library borrowing system built with Laravel 12 (MVC), Blade, Bootstrap 5 and MySQL/SQLite.
Admins manage the catalogue; members browse, borrow and return books.

## Features (excluding authentication)

| # | Feature | Who |
|---|---|---|
| 1 | Category management (create, list, edit, delete) | Admin |
| 2 | Book management (create, list, view, edit, delete) | Admin |
| 3 | Cover image upload, replace and cleanup | Admin |
| 4 | Search books by title/author, filter by category and availability | Everyone |
| 5 | Paginated catalogue | Everyone |
| 6 | Borrow a book (stock check, 3-loan limit, 7-day due date) | Member |
| 7 | Return a book (stock restored, late fine calculated) | Member / Admin |
| 8 | Personal loan history | Member |
| 9 | Dashboard with statistics (admin overview, member summary) | Logged-in |
| 10 | All-loans list with status filter and delete | Admin |
| 11 | Role-based access control (admin / member) | System |

Authentication: register, login, logout (own implementation, no starter-kit dependency).

Database: 4 tables, 3 foreign keys. See [`docs/DATABASE.md`](docs/DATABASE.md) for the ERD and data dictionary.

## Requirements
PHP 8.2+ (with `pdo_mysql` or `pdo_sqlite`, `mbstring`, `fileinfo`), Composer 2, MySQL 8 / MariaDB 10.4+ (or SQLite).
Node.js is **not** needed: Bootstrap is loaded from a CDN.

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
```

**MySQL (XAMPP/Laragon):** create an empty database called `library_db`, check `DB_USERNAME`/`DB_PASSWORD` in `.env`.
**SQLite instead:** in `.env` set `DB_CONNECTION=sqlite`, delete the other `DB_*` lines, then run `touch database/database.sqlite`
(on Windows: create an empty file with that name).

```bash
php artisan migrate --seed      # creates tables + demo data
php artisan storage:link        # needed so uploaded covers are visible
php artisan serve               # http://localhost:8000
```

Demo accounts (created by the seeder, password `password`):

| Role | Email |
|---|---|
| Admin | admin@library.test |
| Member | member@library.test |
| Member | budi@library.test |

Change or delete these accounts before publishing a real site.

## Run the automated tests

```bash
php artisan test
```
The tests (`tests/Feature/LibraryTest.php`) use an in-memory SQLite database and cover registration, login/logout,
role protection, CRUD, search, borrowing rules, stock handling and fine calculation.

## Production notes
- Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, and your database credentials.
- Point the web server's document root at the `public/` folder.
- Run `php artisan migrate --force` and `php artisan storage:link` once on the server.
- On hosts without a free persistent disk, uploaded covers can disappear after a redeploy.
- Seed data is optional: run `php artisan db:seed` once, or create the first admin by setting `role = 'admin'` in the `users` table.

## Deploy to Render (free, with Docker)

The repository contains a `Dockerfile` (+ `docker/`) that works on hosts running Docker, such as Render.

1. Push the project to GitHub (never commit `.env`).
2. On Render create a **PostgreSQL** database (Free) and a **Web Service** from the GitHub repo, runtime **Docker**, instance type **Free**.
3. Set these environment variables on the web service:

| Variable | Value |
|---|---|
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | the `https://...onrender.com` address of the service |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | the *Internal Database URL* of the Render PostgreSQL |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `file` / `file` / `sync` |
| `LOG_CHANNEL` | `stderr` (logs appear in the Render dashboard) |
| `SEED_DEMO` | `true` (creates the demo accounts once; remove afterwards if you like) |

On start the container runs the migrations automatically. Free instances sleep after inactivity and use a temporary disk,
so uploaded cover images are lost when the service restarts. Free Render PostgreSQL databases expire after 30 days.

## Project structure (the parts written for this project)
```
app/Http/Controllers   AuthController, BookController, CategoryController, LoanController, DashboardController
app/Http/Middleware    EnsureAdmin
app/Models             User, Category, Book, Loan
database/migrations    role column, categories, books, loans
database/seeders       DatabaseSeeder (demo data)
resources/views        layouts, auth, books, categories, loans, dashboard, partials
routes/web.php         all routes
tests/Feature          LibraryTest
```
