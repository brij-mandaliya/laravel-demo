# User-Post-Comments — Laravel Application Plan

End-to-end Laravel app: simple community where users write posts and comment on them
with nested replies. Blade + Tailwind (Vite) UI, PostgreSQL for data, deployed to Render.

- **Auth:** Laravel Breeze (Blade)
- **Comments:** nested replies (reply-to-reply trees, capped depth)
- **Frontend:** Blade + Tailwind via Vite
- **Local dev:** Laravel Sail (PHP + PostgreSQL in Docker)
- **Prod:** Render (native build) + managed PostgreSQL

## Stack

| Layer | Choice |
| --- | --- |
| Framework | Laravel 13 (PHP 8.5) |
| Local runtime | Laravel Sail (Docker: `app` + `pgsql`) |
| Database | PostgreSQL everywhere (Docker locally, Render-managed in prod) |
| UI | Blade + Tailwind CSS, compiled with Vite |
| Auth | Laravel Breeze (Blade scaffold) |
| Deployment | Render Blueprint (`render.yaml`), native `composer`/`npm` build |
| Tests | Laravel Feature tests via `sail artisan test` |

## Why Sail for students

Sail gives every student an identical environment in Docker — the local Postgres
matches the managed Postgres on Render exactly, so there is no
"works on my machine, breaks in prod" drift.

---

## Phase 0 — Scaffold project (Sail)

1. `composer create-project laravel/laravel .` (Laravel 13).
2. Install `laravel/breeze` and run `php artisan breeze:install blade`.
3. `php artisan sail:install --with=pgsql` → Compose file with `app` + `pgsql`.
4. `./vendor/bin/sail up -d`; verify `sail artisan --version`.
5. Add Shell alias to project docs / README:
   `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'`
6. `.env` (auto-written by Sail): `DB_CONNECTION=pgsql`, host `pgsql`,
   db `laravel`, user/pass `sail`.
7. `sail npm install && sail npm run build` — confirm Vite + Tailwind compile.

## Phase 1 — Data model

8. Migrations (`sail artisan make:migration`):

   **`posts`**
   | Column | Type | Notes |
   | --- | --- | --- |
   | id | bigint PK | |
   | user_id | FK → users | on delete cascade |
   | title | string(255) | |
   | slug | string, unique | URL-friendly title |
   | body | text | |
   | timestamps | | |

   **`comments`**
   | Column | Type | Notes |
   | --- | --- | --- |
   | id | bigint PK | |
   | user_id | FK → users | on delete cascade |
   | post_id | FK → posts | on delete cascade |
   | parent_id | FK → comments, nullable | self-ref → nested replies |
   | body | text | max 2000 chars |
   | timestamps | | |
   + index on `(post_id, parent_id)`.

9. Models + relationships:
   - `User`: `hasMany Post`, `hasMany Comment`
   - `Post`: `belongsTo User`, `hasMany Comment`
   - `Comment`: `belongsTo User` / `belongsTo Post`, self `parent()`/`children()`,
     `childrenCount` helper.

10. Factories + seeders: fake users, posts, and 2–3 levels of nested comments.
    `sail artisan db:seed` for local dev.

## Phase 2 — Features

11. **Post routes** (resource controller):
    - Public: `index` (paginated, newest first, with comment counts),
      `show` (post + threaded comment tree).
    - Authed: `create` / `store` / `edit` / `update` / `destroy`.
    - Owner-only edits/deletes via a Policy (non-owner → 403).

12. **Comment routes** (controller):
    - `POST /posts/{post}/comments` — top-level, auth required.
    - `POST /posts/{post}/comments/{comment}/reply` — nested reply;
      validates parent belongs to the same post; caps nesting depth (~3)
      to keep trees manageable.
    - `DELETE /posts/{post}/comments/{comment}` — owner only.

13. **Nested rendering**: recursive Blade partial `comment.blade.php`
    rendering the indented tree; avatars + relative timestamps.

14. **Validation (Form Requests)**:
    - Post title: required, 3–255
    - Post body: required, min 10
    - Comment body: required, 1–2000

15. **UI polish**: Tailwind layouts, flash messages for success/error,
    Breeze dashboard as home.

## Phase 3 — Tests (`sail artisan test`)

16. Feature tests:
    - Guest redirected away from post creation.
    - Authenticated user creates a post; it appears on index.
    - Owner can edit/delete; non-owner gets 403.
    - User comments on a post.
    - Nested reply works and is indented under parent.
    - Reply to a comment belonging to another post is rejected.
    - Owner deletes a comment.
    - Threaded tree renders in correct order.

17. Verify suite green before deploying.

## Phase 4 — Render deployment

18. **`render.yaml`** blueprint (native build — Sail is local dev only):
    | Key | Value |
    | --- | --- |
    | buildCommand | `composer install --no-dev --optimize-autoloader` then `npm ci && npm run build` |
    | preDeployCommand | `php artisan migrate --force` |
    | startCommand | `php artisan serve --host=0.0.0.0 --port=$PORT` |
    | env | `APP_ENV=production`, `APP_DEBUG=false` |
    | services | Linked managed **PostgreSQL** instance, `DATABASE_URL` auto-injected |

19. Environment defaults committed in `.env.example`:
    `SESSION_DRIVER=database`, `CACHE_STORE=database`,
    `QUEUE_CONNECTION=database`, `DATABASE_URL`.
    (File-based sessions don't survive Render restarts; DB drivers do.)

## Phase 5 — Verify

20. `git init` already done; first commit; push to GitHub.
21. Deploy via Render Blueprint from the repo.
22. Smoke test after deploy:
    register → login → create post → comment → nested reply → **restart service**
    → data & session persist (Postgres).

---

## Out of scope (future)

- Post likes
- Image / attachment uploads
- Mentions / notifications
- Pagination of deep comment trees
- CI pipeline (GitHub Actions)