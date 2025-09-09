# Waffle Installation Guide <!-- omit in toc -->

This document guides you through setting up the waffle dashboard, making it waterproof with HTTPS and how to upgrade it, if you've been using an old version while eating fresh waffles.

## Table of Contents <!-- omit in toc -->

- [Requirements](#requirements)
- [Basic Setup](#basic-setup)
- [Secure your Waffles (HTPS)](#secure-your-waffles-htps)
- [Waffle Upgrade Guide](#waffle-upgrade-guide)

## Requirements

- have a server
- [Docker](https://docs.docker.com/get-started/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) are installed and operational on your server

Many hosting providers give you both in one go, allowing you to select `Docker CE` instead of a vanilla OS Image in the "Create Server" dialogue. E.g. [Hetzner Cloud](https://www.hetzner.com/de/cloud).

## Basic Setup

SSH into your server.

1. Create a folder
    ```shell
    mkdir /opt/waffle-dashboard
    `cd /opt/waffle-dashboard`
    ```
2. Download the `docker-compose.yml`
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/waffle-dashboard/main/docker-compose.yaml -o docker-compose.yaml
    ```
3. Download the `.env.example` and save it as `env` file
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/waffle-dashboard/main/.env.example -o .env
    ```
4. Edit the `.env` file: `APP_ENV=production`, `APP_DEBUG=false`, and set the `APP_URL` to your domain or public IP address
    ```shell
    nano .env
    ```
5. Create the Docker network and start the services
    ```shell
    docker network create waffle-dashboard-network
    docker compose up -d
    ```
6. Set a newly generated `APP_KEY` in the `.env` file (format `base64:xyz...`)
    ```shell
    docker compose exec php-fpm bash
    php artisan key:generate --show
    exit
    nano .env 
    ```
7. Verify your installation by visting http://_yourServersPublicIPaddress_/health.

Now you can create your first admin user to use the waffle dashboard:

````shell
docker compose exec php-fpm bash
php artisan make:filament-admin
````

## Secure your Waffles (HTPS)

## Waffle Upgrade Guide

> If your waffle dashboard is vintage, come here. Otherwise, you have more important work to do.
