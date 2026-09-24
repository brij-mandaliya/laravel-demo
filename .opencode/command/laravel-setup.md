# Laravel Setup

Sets up a Laravel application from scratch using Laravel Sail (Docker).

## Environment

Assumes: PHP + Composer installed, Docker running.

## Steps

1. Scaffold a fresh Laravel 13 project into the current directory:

   ```bash
   composer create-project laravel/laravel .
   ```

2. Install Laravel Breeze and scaffold the Blade auth UI:

   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   ```

3. Install Sail with PostgreSQL:

   ```bash
   php artisan sail:install --with=pgsql
   ```

   This writes a `docker-compose.yml` with `app` + `pgsql` services.

4. Boot the environment and verify:

   ```bash
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan --version
   ```

5. Add a `sail` alias so plain `sail` works from the shell (document in README):

   ```bash
   alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
   ```

6. Confirm `.env` is wired for PostgreSQL (Sail writes it automatically):
   `DB_CONNECTION=pgsql`, host `pgsql`, db `laravel`, user/pass `sail`.

7. Compile the frontend to verify Vite + Tailwind work:

   ```bash
   sail npm install
   sail npm run build
   ```

## Acceptance

- `sail artisan --version` prints a Laravel 13 version.
- `sail artisan migrate` runs clean against Postgres.
- `sail npm run build` compiles Blade + Tailwind assets without errors.