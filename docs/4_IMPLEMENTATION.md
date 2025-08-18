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
