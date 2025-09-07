# Implementation (Coding) <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [1. New Laravel+Docker project](#1-new-laraveldocker-project)

## 1. New Laravel+Docker project

The official Docker Guide [Develop and Deploy Laravel applications with Docker Compose](https://docs.docker.com/guides/frameworks/laravel/)
provides a fresh Laravel project [dockersamples / laravel-docker-examples](https://github.com/dockersamples/laravel-docker-examples),
that implements exactly the architecture proposed in [3_ARCHITECTURE/4.3 System Structure](3_ARCHITECTURE.md#43-system-structure).

- a Docker Compose stack with
- an `nginx` container
- a `php-fpm` container
- a `postgres` container

It is documented how to work with it in the [Developer Docs](../DEVELOPER-DOCS.md) and the [Base Project README](BASE-PROJECT-README.md).

After adding the fresh Laravel+Docker project (Commit `c7030c2`: `setup: add fresh Laravel+Docker project`),
I removed Redis from it, because I won't need it (Commit `a278548`: `setup: remove Redis`).

## 2. Install Filament and create Artisan command for making admin users

- [install Filament v4](https://filamentphp.com/docs/4.x/introduction/installation)

```shell
composer require filament/filament:"^4.0"
php artisan filament:install --panels
```

- adapt the `User` model to (1) allow all users to access the Filament panel and (2) support admin users.
  - put the `is_admin` flag in the `create_users_table` migration and applied the change via `php artisan migrate:fresh`
- created a `make:filament-admin` artisan command by extending the `Filament\Commands\MakeUserCommand`
- removed the `/` route in `web.php` to allow the filament panel (with `path: ''`) to control that route
- removed redis usage from the `.env` and `.env.example`

After that I created myself an admin user, checked that I could access the panel at `http://localhost` and verified by
looking into the database, that the `is_admin` flag is set to `true` for that user.
