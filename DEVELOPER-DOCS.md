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
- [Usage](#usage)
  - [Accessing the Workspace Container](#accessing-the-workspace-container)
  - [Run Artisan Commands:](#run-artisan-commands)
  - [Rebuild Containers:](#rebuild-containers)
  - [Stop Containers:](#stop-containers)
  - [View Logs:](#view-logs)

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
npm run dev
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

Open your browser and navigate to [http://localhost](http://localhost).

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
