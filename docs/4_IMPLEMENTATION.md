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

After adding the fresh Laravel+Docker project, I removed Redis from it, because I won't need it.

> Commits:
> 
> - `c7030c28`: `setup: add fresh Laravel+Docker project`
> - `a2785485`: `setup: remove Redis`

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

> Commits:
>
> - `03ff8935`: `setup: install filament, implement admin users, remove Redis from .env`

## 3. Add profile page and fully implement the data model

- add `->profile()` in the `MainPanelProvider` to enable Filament v4's built-in profile page for users
- according to the database schema shown in [3_ARCHITECTURE/4.4. Data Model](3_ARCHITECTURE.md#44-data-model), for the `User` and `WaffleEating` entities
  - create/adapt the migrations
  - create/adapt the models
  - create/adapt the factories
  - write the data seeding

Now I just run `php artisan migrate:fresh --seed` and there's my admin user and some more data in the database.

> Commits:
>
> - `fe6b31d0`: `feat: add profile page, setup: complete models, migrations, factories & seeder`

## 4. Add the users CRUD page (only for admins)

- add the Users CRUD (`php artisan make:filament-resource User`) and customize it
- make it only accessible to admins via a UserPolicy (`php artisan make:policy UserPolicy`) and the `UserResource#shouldRegisterNavigation` method

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/resources/overview
- https://filamentphp.com/docs/4.x/tables/overview
- https://filamentphp.com/docs/4.x/forms/overview
- https://filamentphp.com/docs/4.x/resources/overview#authorization
- https://filamentphp.com/docs/4.x/navigation/overview#disabling-resource-or-page-navigation-items
