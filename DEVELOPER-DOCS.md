# Developer Docs <!-- omit in toc -->

> For working on this project, [1_REQUIREMENTS.md](docs/1_REQUIREMENTS.md) and [3_ARCHITECTURE.md](docs/3_ARCHITECTURE.md) serve as a reference.

> Rules: Laravel 12 and Filament 4 as well as regular PHP and overall programming conventions shall be followed.

This project is based on the official [Docker+Laravel Guide](https://docs.docker.com/guides/frameworks/laravel/)'s
repository [dockersamples / laravel-docker-examples](https://github.com/dockersamples/laravel-docker-examples),
whose documentation can be found in the [BASE-PROJECT-README.md](docs/BASE-PROJECT-README.md).
Below I often just repeat the parts of it, that are relevant here.

The past development is documented in the [4_IMPLEMENTATION.md](docs/4_IMPLEMENTATION.md).

## Table of Contents <!-- omit in toc -->

- [Prerequisites](#prerequisites)
- [Commands for Everyday Development](#commands-for-everyday-development)
- [Setting Up the Development Environment](#setting-up-the-development-environment)
- [Seeding Production](#seeding-production)
- [Usage](#usage)
  - [Accessing the Workspace Container](#accessing-the-workspace-container)
  - [Run Artisan Commands:](#run-artisan-commands)
  - [Rebuild Containers:](#rebuild-containers)
  - [Stop Containers:](#stop-containers)
  - [View Logs:](#view-logs)
- [Manual Testing](#manual-testing)
  - [Dashboard](#dashboard)
  - [Leaderboard](#leaderboard)
- [Testing](#testing)
- [How to Release](#how-to-release)

## Prerequisites
Ensure you have Docker and Docker Compose installed. You can verify by running:

```bash
docker --version
docker compose version
```

If these commands do not return the versions, install Docker and Docker Compose using the official documentation: [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/).

## Commands for Everyday Development

> **For the initial setup of the project visit the [Setting Up the Development Environment](#setting-up-the-development-environment) section first.**

```shell
docker compose -f compose.dev.yaml up -d # Start the setup
```

```shell
docker compose -f compose.dev.yaml exec workspace bash
npm run dev
```

Access the application at [http://localhost](http://localhost).

```shell
docker compose -f compose.dev.yaml down # Shut it down
```

```shell
docker compose -f compose.dev.yaml exec workspace bash
  composer install # run this on first checkout
  npm install
  php artisan key:generate --show # paste this on first checkout to .env and restart the setup
  php artisan migrate # to set up the database structure
  php artisan migrate:fresh --seed
  php artisan tinker
  SomeModel::factory()->count(2)->make()->toJson()
  
docker compose -f compose.dev.yaml exec postgres bash
  psql -d app -U laravel # password: secret
  \dt
  \d users
  SELECT * FROM users;
  SELECT SUM(count) FROM waffle_eatings WHERE date_part('year', date) = 2025 AND user_id = (SELECT id FROM users WHERE name = 'Admin');
  SELECT COUNT(DISTINCT date) FROM waffle_eatings WHERE date_part('year', date) = 2025;
```

## Setting Up the Development Environment

For development, we use the `compose.dev.yaml` Docker Compose configuration, which includes an additional workspace container with helpful tools.

1. Copy the .env.example file to .env and adjust any necessary environment variables:

```bash
cp .env.example .env
```

Hint: adjust the `UID` and `GID` variables in the `.env` file to match your user ID and group ID. You can find these by running `id -u` and `id -g` in the terminal.

2. Start the Docker Compose Services:

```bash
docker compose -f compose.dev.yaml up -d
```

3. Install Laravel Dependencies:

```bash
docker compose -f compose.dev.yaml exec workspace bash
composer install
npm install
```

4. Run Migrations:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan migrate
```

5. Generate the application encryption key and restart the setup:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan key:generate
```

```bash
docker compose -f compose.dev.yaml down
docker compose -f compose.dev.yaml up -d
```

```bash
docker compose -f compose.dev.yaml exec workspace bash
npm run dev
```

6. Access the Application:

Open your browser and navigate to [http://localhost](http://localhost). You can create a first admin user [as described here](docs/WAFFLE-INSTALLATION-GUIDE.md#basic-setup) > step 7.

## Seeding Production

Seeding production isn't as easy as running `docker compose exec php-fpm php artisan db:seed --force` on the server [as in the Laravel docs](https://laravel.com/docs/12.x/seeding#forcing-seeding-production).

That's because neither `fakerphp/faker` (the dev dependency most crucial to seeding) nor `composer` itself is included in the `production` Docker image (see the [docker/deployment/php-fpm/Dockerfile](docker/deployment/php-fpm/Dockerfile) -> only the production dependencies get copied from a previous builder stage to the production stage).

Thus, The easiest way to seed production like development here, is exporting the data from the development Postgres database, getting the exported data to the server and importing it there.

> **Warning:** Do this production seeding just for demonstration purposes and delete all that data before serious usage.<br>
> The seeded admin user's credentials are publicly visible in this project's [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) and all other users have the password `password`.

1. Run the waffle-dashboard as described in the [Commands for Everyday Development](#commands-for-everyday-development) section.
2. Export the data from the two main database tables
```shell
docker compose -f compose.dev.yaml exec postgres bash
\copy users TO '/tmp/users.csv' CSV HEADER;
\copy waffle_eatings TO '/tmp/waffle_eatings.csv' CSV HEADER;
exit
exit
```
3. Copy the CSV files from the Postgres container to your local host machine
```shell
docker cp waffle-dashboard-postgres-1:/tmp/users.csv ./users.csv
docker cp waffle-dashboard-postgres-1:/tmp/waffle_eatings.csv ./waffle_eatings.csv
```
4. Transfer it from your local host machine to the server
```shell
scp users.csv waffle_eatings.csv root@lchristmann-1:/opt/waffle-dashboard
```
> Given I have below SSH config in my `~/.ssh/config` file
> 
> ```
> Host lchristmann-1
> HostName yourServersIPAddressHere
> User root
> IdentityFile ~/.ssh/lchristmann-1
> ```
5. SSH into the server and `cd` into the waffle-dashboard's directory
```shell
ssh lchristmann-1
cd /opt/waffle-dashboard
```
6. Copy the CSV files from the server to the Postgres container
```shell
docker cp users.csv waffle-dashboard-postgres-1:/tmp/users.csv
docker cp waffle_eatings.csv waffle-dashboard-postgres-1:/tmp/waffle_eatings.csv
```
7. Backup the data from the users table
```shell
docker compose exec -T postgres psql -U laravel -d app -c "\
COPY (SELECT name, email, email_verified_at, password, is_admin, remember_token, created_at, updated_at FROM users) \
TO STDOUT WITH CSV HEADER" > users_data.csv
```
8. Delete all data from the `users` and `waffle_eatings` tables (else we'd run into conflicts trying to insert records with already existing `id` values)
```shell
docker compose exec postgres psql -d app -U laravel
TRUNCATE TABLE waffle_eatings, users RESTART IDENTITY;
```
9. Import the seeding data to the server's Postgres database
```postgresql
\copy users FROM '/tmp/users.csv' DELIMITER ',' CSV HEADER;
\copy waffle_eatings FROM '/tmp/waffle_eatings.csv' DELIMITER ',' CSV HEADER;
SELECT setval(pg_get_serial_sequence('users','id'), (SELECT MAX(id) FROM users));
SELECT setval(pg_get_serial_sequence('waffle_eatings','id'), (SELECT MAX(id) FROM waffle_eatings));
```
```shell
exit
```
10. Restore the previously present users data
```shell
docker compose exec -T postgres psql -U laravel -d app -c "\
COPY users(name, email, email_verified_at, password, is_admin, remember_token, created_at, updated_at) \
FROM STDIN WITH CSV HEADER" < users_data.csv
```

Done - now you can log in for example with the admin user: `admin@admin.com` and `admin` or any other user with their email and password `password`.

To clean up the temporary files, run below commands:

```shell
rm users.csv waffle_eatings.csv # in this project
rm /opt/waffle-dashboard/users_data.csv /opt/waffle-dashboard/waffle_eatings.csv # on the server
```

## Usage

Here are some common commands and tips for using the development environment:

### Accessing the Workspace Container

The workspace sidecar container includes Composer, Node.js, NPM, and other tools necessary for Laravel development (e.g. assets building).

```bash
docker compose -f compose.dev.yaml exec workspace bash
```

### Run Artisan Commands:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan migrate
```

### Rebuild Containers:

```bash
docker compose -f compose.dev.yaml up -d --build
```

### Stop Containers:

```bash
docker compose -f compose.dev.yaml down
```

### View Logs:

```bash
docker compose -f compose.dev.yaml logs -f
```

For specific services, you can use:

```bash
docker compose -f compose.dev.yaml logs -f web
```

## Manual Testing

Open a shell with the database:

```shell
docker compose -f compose.dev.yaml exec postgres bash
psql -d app -U laravel
```

Then validate the metrics shown on the dashboard by comparing results from the queries below against them.

Set the year to run queries against:

```postgresql
\set year 2025
```

### Dashboard

Check the stats:

```postgresql
-- Waffles Eaten (2025)
SELECT COALESCE((SELECT SUM(count) FROM waffle_eatings WHERE date_part('year', date) = :year), 0)
    + COALESCE((SELECT SUM(count) FROM remote_waffle_eatings WHERE date_part('year', date) = :year AND approved_by IS NOT NULL), 0) AS sum;
--  Office Waffles (2025)
SELECT COALESCE(SUM(count), 0) FROM waffle_eatings WHERE date_part('year', date) = :year;
-- Remote Waffles Eaten (2025)
SELECT COALESCE(SUM(count), 0) FROM remote_waffle_eatings WHERE date_part('year', date) = :year AND approved_by IS NOT NULL;
-- People Participated (2025)
SELECT COUNT(DISTINCT user_id)
FROM (
    SELECT user_id FROM waffle_eatings WHERE date_part('year', date) = :year
    UNION ALL
    SELECT user_id FROM remote_waffle_eatings WHERE date_part('year', date) = :year AND approved_by IS NOT NULL
    ) AS all_participants;
-- Waffle Days (2025)
SELECT COUNT(DISTINCT date)
FROM (
     SELECT date FROM waffle_eatings WHERE EXTRACT(YEAR FROM date) = :year
     UNION ALL
     SELECT date FROM remote_waffle_eatings WHERE EXTRACT(YEAR FROM date) = :year AND approved_by IS NOT NULL
) AS all_dates;
```

Check the charts below the stats (not the big widgets, but the helper chart at the bottom of the stat boxes):

```php
// Search the project for "Insert debug snippet BN7C here" and insert this
Log::debug("Waffle Stats month {$month}", [
    'office' => $officeWaffles,
    'remote' => $remoteWaffles,
    'total' => $officeWaffles + $remoteWaffles,
    'people' => $mergedPeople,
    'days'   => $mergedDays,
]);
```

Reload the dashboard page and you can roughly validate the rows printed in the `storage/log/laravel.log` like so:

> You can straight check the logged `office` and `remote` values against the below queries, but the logged `people` and `days` values
> are from both of those combined (but deduplicated, so you can't just add up).  Just know this and verify that they make general sense.

```postgresql
-- Office waffles by month
SELECT EXTRACT(MONTH FROM date) AS month, SUM(count) AS total_waffles, COUNT(DISTINCT user_id) AS people, COUNT(DISTINCT date) AS days
FROM waffle_eatings
WHERE date_part('year', date) = :year
GROUP BY month
ORDER BY month;
-- Remote waffles by month (approved only)
SELECT EXTRACT(MONTH FROM date) AS month, SUM(count) AS total_waffles, COUNT(DISTINCT user_id) AS people, COUNT(DISTINCT date) AS days
FROM remote_waffle_eatings
WHERE date_part('year', date) = :year AND approved_by IS NOT NULL
GROUP BY month
ORDER BY month;
```

Check the chart widgets:

WafflesEatenChart:

```php
// Search the project for "Insert debug snippet MVR5 here" and insert this
Log::debug("Waffles Eaten chart month {$month}", [
    'office' => $office,
    'remote' => $remote,
    'total' => $office + $remote,
]);
```

The data returned here should equal that logged with the above php snippet BN7C.

WaffleDayParticipationsChart:

```php
// Search the project for "Insert debug snippet Q2ST here" and insert this
Log::debug("Waffle Day Participations chart month {$month}", [
    'office' => count($officePeople),
    'remote' => count($remotePeople),
    'total' => $uniquePeopleCount,
]);
```

You can compare the results to the two SQL queries above (`-- Office waffles by month` and `-- Remote waffles by month`):

- the `people` column from above `--office` query should equal the logs of the `office` value
- the `people` column from above `--remote` query should equal the logs of the `remote` value

### Leaderboard

Set the year to run queries against:

```postgresql
\set year 2025
```

Validate that the people in the leaderboard do have the amount of waffles as the data suggests:

```postgresql
-- Waffle Eatings (Office + Remote)
SELECT COALESCE((SELECT SUM(count) FROM waffle_eatings WHERE user_id = (SELECT id FROM users WHERE name = 'Mrs. Angelina Luettgen II') AND date_part('year', date) = :year), 0)
           + COALESCE((SELECT SUM(count) FROM remote_waffle_eatings WHERE user_id = (SELECT id FROM users WHERE name = 'Mrs. Angelina Luettgen II') AND date_part('year', date) = :year AND approved_by IS NOT NULL), 0) AS sum;
-- Waffle Eatings (Office) for a user in the given year
SELECT COALESCE(SUM(count), 0) FROM waffle_eatings WHERE user_id = (SELECT id FROM users WHERE name = 'Dr. Wayne Ondricka IV') AND date_part('year', date) = :year;
-- Waffle Eatings (Remote) for a user in the given year
SELECT COALESCE(SUM(count), 0) FROM remote_waffle_eatings WHERE user_id = (SELECT id FROM users WHERE name = 'Mrs. Angelina Luettgen II') AND date_part('year', date) = :year AND approved_by IS NOT NULL;
```

## Testing

Execute the unit tests written for the [FormatsNumbers](/app/Traits/FormatsNumbers.php) trait the with [Pest](https://pestphp.com/):

```bash
docker compose -f compose.dev.yaml exec workspace bash
php artisan test --testsuite=Unit
```

## How to Release

> ⚠️ Test that whatever you've changed did not break the application: just run the below build command,
> set that new release version in the `docker-compose.yaml` file
> and then do `docker compose -f docker-compose.yaml up -d` here locally.

```shell
docker login
```

Set a version that you want to release:

```shell
VERSION=1.0.0
```

Then build, tag and push the docker image:

```shell
docker build \
  -f ./docker/deployment/php-fpm/Dockerfile \
  -t leanderchristmann/waffle-dashboard:${VERSION} \
  -t leanderchristmann/waffle-dashboard:latest \
  .
```

```shell
docker push leanderchristmann/waffle-dashboard:${VERSION}
docker push leanderchristmann/waffle-dashboard:latest
```

Set that new version in the `docker-compose.yaml`:

```yaml
  php-fpm:
    # For the php-fpm service, we will create a custom image to install the necessary PHP extensions and setup proper permissions.
    image: leanderchristmann/waffle-dashboard:1.0.0 # <--- here!!
```

Now commit your changed code.

Also tag the Git release:

```shell
git tag -a "${VERSION}" -m "Release ${VERSION}"
git push origin "${VERSION}"
```

Finally, [create a GitHub release](https://github.com/lchristmann/selfhosted-waffle-dashboard/releases) via the GitHub UI -
it's takes the Git tag and lets you add some meta-information to it.
Give a title like `1.0.0`, a heading like `## What's Changed` and put a bullet point list of changes.
