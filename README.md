# Taskforge API

![CI](https://github.com/pGabrielM/taskforge-api/actions/workflows/ci.yml/badge.svg)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Sanctum-4169E1?style=flat-square)

REST API for task management: Sanctum-authenticated, user-scoped tasks, with a public read-only
endpoint for showcasing tasks without login.

## Why this project

A Laravel API kept close to the framework's own conventions (Form Requests for validation,
Sanctum for stateless token auth, policy-free ownership checks in the controller) with a real
test suite (27 tests / 110 assertions) instead of a demo with no coverage — the part that
actually signals whether the code can be trusted to change safely.

## Architecture

- **`AuthController`**: register, login (issues a Sanctum token) and logout (revokes the current
  token).
- **`UserController`**: the authenticated user manages their own profile (`show`, `update`,
  `delete`) — no admin/other-user access exists by design.
- **`TaskController`**: `listPublic` is unauthenticated (read-only, for demos); every other task
  route is scoped to `auth()->user()` at the query level, so a user can never see or mutate
  another user's tasks even by guessing an ID.
- **Form Requests** (`app/Http/Requests`) own validation, keeping controllers thin.

## Endpoints

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| `POST` | `/login` | — | Authenticates, returns a Sanctum token. |
| `POST` | `/register` | — | Creates a user. |
| `GET` | `/task` | — | Lists tasks marked public (demo/showcase). |
| `POST` | `/logout` | ✅ | Revokes the current token. |
| `GET` | `/user` | ✅ | Returns the authenticated user. |
| `PUT` | `/user` | ✅ | Updates the authenticated user. |
| `DELETE` | `/user` | ✅ | Deletes the authenticated account. |
| `GET` | `/user/task` | ✅ | Lists the authenticated user's tasks. |
| `GET` | `/user/task/{id}` | ✅ | Shows one of the user's tasks. |
| `POST` | `/user/task` | ✅ | Creates a task. |
| `PUT` | `/user/task/{id}` | ✅ | Updates a task. |
| `DELETE` | `/user/task/{id}` | ✅ | Deletes a task. |

## Running locally

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

The repo ships a `docker-compose.yml` for [Laravel Sail](https://laravel.com/docs/sail)
(PostgreSQL + Mailpit) if you'd rather not install PHP/Postgres locally: `./vendor/bin/sail up`.

## Tests & CI

```bash
php artisan test          # 27 tests, 110 assertions — Feature + Unit
./vendor/bin/pint --test  # code style (Laravel Pint)
```

Every push/PR to `main` runs Pint and the full test suite against a real PostgreSQL service via
[GitHub Actions](.github/workflows/ci.yml) — not SQLite, to match production.

## Stack

PHP, Laravel 11, Laravel Sanctum, PostgreSQL, Docker (Sail), PHPUnit, Laravel Pint.
