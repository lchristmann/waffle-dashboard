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

...

Now you can create your first admin user:

````shell
docker compose exec php-fpm bash
php artisan make:filament-admin
````

## Secure your Waffles (HTPS)

## Waffle Upgrade Guide

> If your waffle dashboard is vintage, come here. Otherwise, you have more important work to do.
