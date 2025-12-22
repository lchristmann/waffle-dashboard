# Waffle Installation Guide <!-- omit in toc -->

This document guides you through setting up the waffle dashboard, making it waterproof with HTTPS and how to upgrade it, if you've been using an old version while eating fresh waffles.

## Table of Contents <!-- omit in toc -->

- [Requirements](#requirements)
- [Basic Setup](#basic-setup)
- [Secure and Polish your Waffles (HTTPS and a Domain)](#secure-and-polish-your-waffles-https-and-a-domain)
  - [In Your Domain Provider](#in-your-domain-provider)
  - [On Your Server](#on-your-server)
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
    cd /opt/waffle-dashboard
    ```
2. Download the `docker-compose.yaml` and the nginx `default.conf` file:
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/waffle-dashboard/main/docker-compose.yaml -o docker-compose.yaml
   curl -L https://raw.githubusercontent.com/lchristmann/waffle-dashboard/main/docker/deployment/nginx/default.conf -o docker/deployment/nginx/default.conf
    ```
   > You can customize the server port that the waffle-dashboard will be running on by changing one line in the `docker-compose.yaml`.<br>
   > If you change the `"${NGINX_PORT:-80}:80"` under the `web` service e.g. to `"8080:80"`, it will run on port 8080 on the server.
3. Download the `.env.example` and save it as `env` file
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/waffle-dashboard/main/.env.example -o .env
    ```
4. Edit the `.env` file: `APP_ENV=production`, `APP_DEBUG=false`, and set the `APP_URL` to your server's public IP address (prefixed with `http://`).
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
   ```shell
   # necessary for the application key in the .env to be recognized
   docker compose down
   docker compose up -d 
    ```
7. Visit the app now at http://yourServerPublicIP (specify your port if it's not 80!).

Now you can create your first admin user to use the waffle dashboard and log in:

````shell
docker compose exec php-fpm bash
php artisan make:filament-admin
````

## Secure and Polish your Waffles (HTTPS and a Domain)

>  This guide uses the [Nginx Proxy Manager](https://nginxproxymanager.com/) to enable HTTPS. While that is currently my favourite, feel free to use other solutions like [Caddy](https://caddyserver.com/), [Traefik](https://traefik.io/traefik/), or manual [Nginx](https://nginx.org/) setup instead, if you like to.

Requirement: **you must have a sub(domain)** for HTTPS, since [Let's Encrypt](https://letsencrypt.org/) Certificate Authority doesn’t issue certificates for IP addresses and browsers don’t trust them anyway. So get one, if you don't have one yet.

### In Your Domain Provider

1. Point your (sub)domain to your server's public IP, e.g.
   ```text
   A waffles.example.com 192.168.1.1
   ```

### On Your Server

**Recommended Firewall:**

| Source          | Protocol | Port | Purpose |
|-----------------|----------|------|---------|
| Any IPv4 / IPv6 | TCP      | 22   | SSH     |
| Any IPv4 / IPv6 | ICMP     | -    | Ping    |
| Any IPv4 / IPv6 | TCP      | 80   | HTTP    |
| Any IPv4 / IPv6 | TCP      | 443  | HTTPS   |

SSH into your server.

1. Edit the `.env` file and set the `APP_URL` to your (sub)domain prefixed with `https://` and add a variable `ASSET_URL` with the same value.
   ```shell
   cd /opt/waffle-dashboard
   nano .env
   ```
2. Edit the `docker-compose.yaml` file and comment out the entire ports section. (This ensures the `web` service is only reachable internally in the `waffle-dashboard-network` Docker network)
   ```shell
   nano docker-compose.yaml
   ```
   ```yaml
   # ports:
     # - "${NGINX_PORT:-80}:80"
   ```
3. Restart the setup with `down` and `up` for the `.env` file change to take effect
   ```shell
   docker compose down
   docker compose up -d
   ```
4. Set up the [Nginx Proxy Manager](https://nginxproxymanager.com/)
    1. Create a folder
       ```yaml
       mkdir /opt/nginx-proxy
       cd /opt/nginx-proxy
       ```
    2. Create a `docker-compose.yaml` file like this (from the [official Quick Setup guide](https://nginxproxymanager.com/guide/#quick-setup))
       ```yaml
       touch docker-compose.yaml
       nano docker-compose.yaml
       ```
       ```yaml
       services:
         app:
           image: 'jc21/nginx-proxy-manager:latest'
           restart: unless-stopped
           ports:
             - '80:80'
             - '81:81'
             - '443:443'
           volumes:
             - ./data:/data
             - ./letsencrypt:/etc/letsencrypt
       ```
       Also paste this, to put the Nginx Proxy Manager into the `waffle-dashboard-network` Docker network.
       The Proxy Manager is the single point of access to the Waffle Dashboard application now.
       ```yaml
           # This one level nested under the 'app' service
           networks:
             - waffle-dashboard-network
 
       # This on the root level, not nested at all
       networks:
         waffle-dashboard-network:
           external: true
       ```
    3. Bring it up by running
       ```yaml
       docker compose up -d
       ```
    4. Temporarily open port 81 in your firewall to use the Nginx Proxy Manager admin interface

       |                    | Protocol | Port | Note                       |
             |--------------------|----------|------|----------------------------|
       | Any IPv4, Any IPv6 | TCP      | 81   | for Nginx Proxy Manager    |
    5. Access the Admin UI at http://yourServersIP:81

       |          | Default credentials |
             |----------|---------------------|
       | Email    | admin@example.com   |
       | Password | changeme            |
       You'll be prompted to change those credentials immediately after logging in.
    6. Find the container name of your Waffle Dashboard's `web` service, usually `waffle-dashboard-web-1`.
       ```shell
       cd /opt/waffle-dashboard
       docker ps --format "{{.Names}} {{.Image}}" | grep "nginx:alpine" | awk '{print $1}'
       ```
    7. In the Nginx Proxy Manager visit Dashboard > Proxy Hosts > Add Proxy Hosts:
        - "Details" Tab

          | Setting               | Value                      | Example                   | Explanation                                                                        |
                   |-----------------------|----------------------------|---------------------------|------------------------------------------------------------------------------------|
          | Domain Names          | your domain name           | `waffles.example.com`     |                                                                                    |
          | Scheme                | http (yes!!)               |                           | This is only Docker network internal, we'll force HTTPS for internet traffic later |
          | Forward Hostname / IP | the `web` container's name | `waffle-dashboard-web-1`  | See previous step                                                                  |
          | Forward Port          | 80                         |                           |                                                                                    |
          | Block Common Exploits | yes                        |                           |                                                                                    |

        - "SSL" Tab

          | Setting                                       | Value                         | Explanation                                                            |
                  |-----------------------------------------------|-------------------------------|------------------------------------------------------------------------|
          | SSL Certificate                               | Request a new SSL Certificate |                                                                        |
          | Force SSL                                     | yes                           |                                                                        |
          | HTTP/2 Support                                | yes                           | Enables the newer, faster HTTP/2 protocol over TLS                     |
          | HSTS Enabled                                  | yes                           | Adds `Strict-Transport-Security` header to force browsers to use HTTPS |
          | I agree to the Let's Encrypt Terms of Service | yes                           |                                                                        |

        - Click "Save"
    8. Now the application will be available under your domain (prefixed with `https://`).
    9. Remove the temporary firewall rule of allowing port 81.

## Waffle Upgrade Guide

> If your waffle dashboard is vintage, come here. Otherwise, you have more important work to do.

SSH into your server.

1. Update the `leanderchristmann/waffle-dashboard` docker image version in the `docker.yaml` file to the current (or some other desired) release:
   ```shell
   cd /opt/waffle-dashboard
   nano docker-compose.yaml
   ```
   Example:
   ```yaml
   php-fpm:
     # For the php-fpm service, we will create a custom image to install the necessary PHP extensions and setup proper permissions.
     image: leanderchristmann/waffle-dashboard:2.0.0  # <--- here!!
   ```
2. Restart the setup with `down` and `up`. All data will be kept due to Docker volumes' persistence.
   ```shell
   docker compose down
   docker compose up -d
   ```

All done - it's that simple.
