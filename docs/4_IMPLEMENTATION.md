# Implementation (Coding) <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [1. New Laravel+Docker project](#1-new-laraveldocker-project)
- [2. Install Filament and create Artisan command for making admin users](#2-install-filament-and-create-artisan-command-for-making-admin-users)
- [3. Add profile page and fully implement the data model](#3-add-profile-page-and-fully-implement-the-data-model)
- [4. Add the users CRUD page (only for admins)](#4-add-the-users-crud-page-only-for-admins)
- [5. Enhance the users CRUD page (only for admins)](#5-enhance-the-users-crud-page-only-for-admins)
- [6. Fix user -\> waffleEating FK relation](#6-fix-user---waffleeating-fk-relation)
- [7. Add the waffles CRUD page](#7-add-the-waffles-crud-page)
- [8. Add the leaderboard page](#8-add-the-leaderboard-page)
- [9. Build the dashboard - first custom widget](#9-build-the-dashboard---first-custom-widget)
- [10. Build the dashboard - 3 stats overview widget](#10-build-the-dashboard---3-stats-overview-widget)
- [11. Build the dashboard - 2 chart widgets](#11-build-the-dashboard---2-chart-widgets)
- [12. Improve the dashboard - have a global year filter](#12-improve-the-dashboard---have-a-global-year-filter)
- [13. Finished application!! Let's have screenshots.](#13-finished-application-lets-have-screenshots)
- [14. Release the software (`v1.0.0`)](#14-release-the-software-v100)
- [15. Improve Installation Speed and Software Security through a better Build Process (`v1.1.0`)](#15-improve-installation-speed-and-software-security-through-a-better-build-process-v110)
- [16. Improve the User Interface (UI) \& the User Experience (UX)](#16-improve-the-user-interface-ui--the-user-experience-ux)
- [17. Improve Application Performance (Dashboard, Leaderboard)](#17-improve-application-performance-dashboard-leaderboard)
- [18. Upload new screenshots (`v1.2.0`)](#18-upload-new-screenshots-v120)
- [19. Further Improve the User Experience (UX) (`v1.3.0`)](#19-further-improve-the-user-experience-ux-v130)
- [20. Custom Waffle Dashboard: Release your own one!](#20-custom-waffle-dashboard-release-your-own-one)
- [21. Add Waffle Day Events](#21-add-waffle-day-events)

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

> Commits:
>
> - `6f7e9b71`: `feat: add the users CRUD page (only for admins)`

## 5. Enhance the users CRUD page (only for admins)

There are [Relation Managers](https://filamentphp.com/docs/4.x/resources/managing-relationships#relation-managers---interactive-tables-underneath-your-resource-forms)
in Filament, which are interactive tables below resources that allow managing related records without leaving
the resource's Edit or View page.

Let's use that to always have a table of all WaffleEating records below the User View/Edit form,
when the admin  clicks on a user on the CRUD page. Very practical, to be able to manage it here straight-away!

- create the [relationship manager](https://filamentphp.com/docs/4.x/resources/managing-relationships#creating-a-relation-manager) (`php artisan make:filament-relation-manager UserResource waffleEatings date`)

> Commits:
>
> - `e425416a`: `feat: add relationship manager to the users CRUD page (only for admins)`

## 6. Fix user -> waffleEating FK relation

- it's supposed to be a `user_id` for who ate the waffles and the `entered_by_user_id` for who entered the WaffleEating record -> implement that fix
- remove the vite server (`npm install` & `npm run dev`) from the developer docs, because we don't need/use it (Filament v4’s core CSS and JS assets are prebuilt and published in the `public` folder)

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/actions/create#customizing-data-before-saving
- https://filamentphp.com/docs/4.x/tables/overview#accessing-related-data-from-columns

> Commits:
>
> - `1288e4cc`: `fix: use user_id for who ate the waffles & entered_by_user_id for who entered the record`

## 7. Add the waffles CRUD page

- add the WaffleEating CRUD (`php artisan make:filament-resource WaffleEating`) and customize it
  - implement search and filters for the CRUD table
- restrict access via a WaffleEatingPolicy (`php artisan make:policy WaffleEatingPolicy`)
- have the `created_at` and `updated_at` columns in all resource tables, but hidden by default
- fix the `DatabaseSeeder.php`: while creating the 20 random WaffleEating records, it created 2 x 20 new users (unintended)
- after create/edit of a record, redirect to the listing table (implemented in `MainPanelProvider.php`)

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/resources/creating-records#customizing-data-before-saving
- https://filamentphp.com/docs/4.x/resources/editing-records#customizing-data-before-saving
- https://filamentphp.com/docs/4.x/tables/filters/overview
- https://filamentphp.com/docs/4.x/tables/filters/select
- https://filamentphp.com/docs/4.x/tables/filters/query-builder#creating-custom-operators (`->query()` method)
- https://filamentphp.com/docs/4.x/tables/columns/overview#making-toggleable-columns-hidden-by-default

Interesting observation:

Here, setting the `entered_by_user_id` using the `->mutateDataUsing()` function as done in the previous commit in the `WaffleEatingsRelationManager.php` did not work at all.

- I tried chaining that method on the `CreateAction::make()` in `Pages/ListWaffleEatings.php`, where it is defined, but it did not work.
- Similarly, chaining it on the `EditAction::make()` in `Tables/WaffleEatingsTable.php` did do anything.

The solution that worked was to define `mutateFormDataBeforeCreate()` and `mutateFormDataBeforeSave()` methods in the
`Pages/CreateWaffleEating.php` and `Pages/EditWaffleEating.php`.

> Commits:
>
> - `d36e6264`: `feat: add the waffles CRUD page`

## 8. Add the leaderboard page

- add the leaderboard as a Custom Page in Filament (`php artisan make:filament-page Leaderboard`)

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/navigation/custom-pages
- https://filamentphp.com/docs/4.x/tables/custom-data
- https://filamentphp.com/docs/4.x/tables/columns/overview#setting-the-state-of-a-column

> Commits:
>
> - `59e6205c`: `feat: add the leaderboard page`

## 9. Build the dashboard - first custom widget

- add a custom dashboard page and set up a responsive 12-column widget grid
- add the Filament built-in AccountWidget
- add the custom QuoteWidget (`php artisan make:filament-widget QuoteWidget` -> custom, in a panel)
  - set up Tailwind CSS (by having a custom Filament theme) to process classes in my `resources/views/filament` folder and include them in the compiled CSS

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/widgets/overview
- https://filamentphp.com/docs/4.x/widgets/overview#customizing-the-dashboard-page
- https://filamentphp.com/docs/4.x/widgets/overview#responsive-widgets-grid
- https://filamentphp.com/docs/4.x/styling/overview#using-tailwind-css-classes-in-your-blade-views-or-php-files

> Commits:
>
> - `1f883f4e`: `minor: sort sidebar menu items`
> - `a299c841`: `feat: add the dashboard page with 1 built-in and 1 custom widget, setup: Tailwind CSS`

## 10. Build the dashboard - 3 stats overview widget

- add a WaffleStatsOverview widget (`php artisan make:filament-widget WaffleStatsOverview --stats-overview`) that displays "statistics about waffles eaten and participation this year"

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/widgets/stats-overview

> Commits:
>
> - `d696feea`: `feat: add the 3 stats overview widget to the dashboard`

## 11. Build the dashboard - 2 chart widgets

- add the two line chart widgets with a year filter
  - `php artisan make:filament-widget WafflesEatenChart --chart` -> in a panel, line chart
  - `php artisan make:filament-widget WaffleDayParticipationsChart --chart` -> same as above

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/widgets/charts
- https://filamentphp.com/docs/4.x/widgets/charts#custom-filters
  - https://filamentphp.com/docs/4.x/forms/select
- https://filamentphp.com/docs/4.x/widgets/charts#setting-chart-configuration-options
- https://www.chartjs.org/docs/latest/charts/line.html
- https://stackoverflow.com/questions/37699485/skip-decimal-points-on-y-axis-in-chartjs

> Commits:
>
> - `6388e8d5`: `feat: add the 2 line chart widgets to the dashboard`

## 12. Improve the dashboard - have a global year filter

So far the 3 stats overview widgets are pinned to the current year, while the 2 line chart widgets are dynamic.
Let's have one year filter for the whole dashboard (using it across all data-displaying widgets)!

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/widgets/overview#filtering-widget-data

> Commits:
>
> - `6fff59c0`: `feat: add global year filter to the dashboard, remove 2 widget-specific filters`

## 13. Finished application!! Let's have screenshots.

For you to be able to get a quick impression of how the waffle dashboard software looks like, I'll add some images. 

> Commits:
>
> - `1f687d08`: `docs: upload screenshots of the application`

## 14. Release the software (`v1.0.0`)

> In  a failed attempt, I tried to extend the [base project](BASE-PROJECT-README.md) (see [1. step New Laravel+Docker project](#1-new-laraveldocker-project))
> as it was intended. However, since I added the custom widget in [9. Build the dashboard - first custom widget](#9-build-the-dashboard---first-custom-widget),
> this was no longer easily feasible.
> 
> The reason is that the Tailwind CSS we set up in that step (which runs during `npm build`),
> has dependencies on the Filament in the `vendor` folder.
> 
> So I'll simply do the `composer install` and `npm install && npm run build` both in one `Dockerfile` and won't need a custom Nginx Docker image anymore. 
> 
> Commits:
>
> - `8eb08396`: `release+docs: build & push Docker images, add release & setup instructions, adapt nginx image`
> - `061956a6`: `Revert "release+docs: build & push Docker images, add release & setup instructions, adapt nginx image"`

- add a `docker-compose.yaml` and further setup files in the `docker/deployment` directory
  - test it locally (build the Docker image (see [release guide](../DEVELOPER-DOCS.md#how-to-release): just build, don't push) and run `docker compose -f docker-compose.yaml up -d`)
- publish the `leanderchristmann/waffle-dashboard` Docker image
- add a [release guide](../DEVELOPER-DOCS.md#how-to-release)
- write the [waffle installation guide](WAFFLE-INSTALLATION-GUIDE.md)

Some helpful documentation pages:

- https://www.atlantic.net/dedicated-server-hosting/how-to-deploy-laravel-with-docker-and-docker-compose/
- https://stackoverflow.com/questions/76420466/laravel-vite-production-doesnt-use-https

> Commits:
>
> - `a0588101`: `release+docs: add release & deployment code+instructions`
> - `d7619cc8`: `minor+docs: add last step's commit info in implementation guide`

## 15. Improve Installation Speed and Software Security through a better Build Process (`v1.1.0`)

Implement a [multi-stage build](https://docs.docker.com/build/building/multi-stage/) approach (like in the [base project](BASE-PROJECT-README.md)) in the `deployment/php-fpm/Dockerfile`:

- a `builder` stage (with everything: build tools, dev libraries, temporary build artifacts) and
- a slim `production` stage (just the runtime libs + PHP extensions + app code + compiled assets)

Now when I build the image, all the baggage from the builder stage gets left behind, resulting in:

- a smaller `leanderchristmann/waffle-dashboard` Docker image (went down from `365.01 MB` to `276.8 MB`) and thus significantly faster installation time
- improved security, since attackers can't exploit all the previously included dev tools and libraries anymore

> Commits:
>
> - `5080131b`: `build: add multi-stage Docker build for deployment version of the software`

## 16. Improve the User Interface (UI) & the User Experience (UX)

After using the application for some time and being inspired by features encountered in the [Filament 4 From Scratch course by Laravel Daily](https://laraveldaily.com/course/filament-4),
some UI/UX improvements are necessary:

- remove unwelcome dashboard elements AccountWidget & QuoteWidget from the Dashboard UI + do some minor text changes
- make the WaffleEating a simple (modal) resource instead of a normal resource for a better (faster, simpler) user experience
- add more intelligent defaults, validation and sorting to waffle eating form inputs
- add WaffleEating create-action to dashboard + a bulk-create-action to both the dashboard and the resource
  - add keybindings and therefor tooltips for both actions in the dashboard header
- add User bulk-create-action to its resource

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/resources/overview#simple-modal-resources
- https://filamentphp.com/docs/4.x/actions/create#customizing-data-before-saving
- https://filamentphp.com/docs/4.x/actions/edit#customizing-data-before-saving
- https://filamentphp.com/docs/4.x/actions/modals#rendering-a-schema-in-a-modal
- https://filamentphp.com/docs/4.x/forms/repeater
- [[YouTube] Filament: Action Class Re-Used in Multiple Forms by Filament Daily](https://www.youtube.com/watch?v=74h-HiPeKP0)

> Commits:
>
> - `7da2d1b4`: `minor: remove Account- & QuoteWidget from dashboard, text changes`
> - `b2638746`: `feat: make WaffleEating a simple (modal) resource`
> - `92fa1768`: `feat: add create & bulk-create actions for waffle eatings (in dashboard+resource) + improve forms`
> - `22161628`: `feat: add bulk-create action for users + add keybinding for dashboard actions`

## 17. Improve Application Performance (Dashboard, Leaderboard)

I noticed that the dashboard page was loading very slow, so I chose to...

- install the [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) and found that there's 500+ database queries executed loading the page - a major performance problem
  - I found that the widgets had code like `User::all()` inside a `for` loop and loading a relationship there even
    - (for 12 months and 16 users that's already 192 queries + 12 queries retrieving all users)
- I resolved all those issues by aggregating data beforehand, finding more efficient ways and lazy loading filter options via callbacks

Now there's only 10 database queries left, resulting in a nice and fast page load experience.

On this occasion, I also optimized the Leaderboard page (now running one query for all users, instead of one for each!). 

> Commits:
>
> - `5e41eca5`: `perf(dashboard,leaderboard): speed up page load times by massively reducing number of database queries`

## 18. Upload new screenshots (`v1.2.0`)

- upload new screenshots of the application

> Commits:
>
> - `bb786c22`: `docs: upload new screenshots`
> - `4897c2db`: `release: v1.2.0`

## 19. Further Improve the User Experience (UX) (`v1.3.0`)

In practice, there'll be users, who don't manage their own waffle eatings entries, but let others enter them.<br>
Hence the users bulk create would be too cumbersome, if it didn't allow creation just by name.

Therefor we need the name to be unique. Email and password can be set (together, forming a pair of credentials), but don't have to.

Extra: for showcasing purposes I added instructions on how to transfer seeding data into a production environment.

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/forms/overview#conditionally-making-a-field-required
- https://stackoverflow.com/questions/8289100/create-unique-constraint-with-null-columns

> Commits:
>
> - `3789378c`: `feat: make name unique & email optional for users who don't want to log in + release: v1.3.0`

## 20. Custom Waffle Dashboard: Release your own one!

Some waffle dashboard operators will want to customize the software, e.g. tailor the color scheme to their company's color scheme or to their own preferences.

Therefor, I added guide that shows how to release and use one's own customized (private) version of the waffle-dashboard.

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/styling/overview
- https://stackoverflow.com/questions/10065526/github-how-to-make-a-fork-of-public-repository-private
- https://stackoverflow.com/questions/37685581/how-to-get-docker-compose-to-use-the-latest-image-from-repository

> Commits:
>
> - `ef8068aa`: `docs(release): how to release and use a customized (private) version of the software`

## 21. Add Waffle Day Events

GitHub Issue: https://github.com/lchristmann/waffle-dashboard/issues/4

- add the backend functionality to manage Waffle Day Events + the frontend announcement banner
- restrict the WaffleDay resource's management to Waffle Dashboard admins
- improve the WaffleEating DatePicker components by setting today's (or the most recent) Waffle Day as the primary default

```shell
php artisan make:model WaffleDay -mf
php artisan make:filament-resource WaffleDay --simple --generate
php artisan make:policy WaffleDayPolicy --model=WaffleDay
```

Some helpful documentation pages:

- https://filamentphp.com/docs/4.x/advanced/render-hooks
- https://filamentphp.com/docs/4.x/forms/date-time-picker
- https://tailwindcss.com/plus/ui-blocks/marketing/elements/banners
- https://alpinejs.dev/directives/show
