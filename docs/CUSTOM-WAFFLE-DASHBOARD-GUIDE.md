# Custom Waffle Dashboard Guide <!-- omit in toc -->

You can customize the Waffle dashboard according to your preferences.<br>
I.e. application name, logo, favicon, primary color, page titles,...

> If you're comfortable with it being public, then a public fork via GitHub UI will do.<br>
> You can skip all the contents here regarding the management of a private repository.

This guide shows you how to have a private fork of the `lchristmann/waffle-dashboard`,
publish your own Docker image and run that instead of the standard (public) waffle-dashboard version.

> It's derived from the StackOverflow post "[GitHub: How to make a fork of public repository private?](https://stackoverflow.com/questions/10065526/github-how-to-make-a-fork-of-public-repository-private)"

## Table of Contents <!-- omit in toc -->

- [Requirements](#requirements)
- [Duplicate the waffle-dashboard to a private repo](#duplicate-the-waffle-dashboard-to-a-private-repo)
- [Clone the private repo and customize the waffle-dashboard](#clone-the-private-repo-and-customize-the-waffle-dashboard)
  - [General Advice](#general-advice)
  - [The App Name](#the-app-name)
  - [The App's Logo](#the-apps-logo)
  - [The Favicon](#the-favicon)
  - [The App's Colors](#the-apps-colors)
  - [Page Titles](#page-titles)
    - [Custom Pages (Dashboard, Leaderboard)](#custom-pages-dashboard-leaderboard)
    - [Resource Pages (Waffles, Users)](#resource-pages-waffles-users)
- [Pulling Updates from the Public Repository (optional)](#pulling-updates-from-the-public-repository-optional)
- [Publish and Use your Waffle Dashboard as Private Docker Image](#publish-and-use-your-waffle-dashboard-as-private-docker-image)
  - [On your PC](#on-your-pc)
  - [On the Server](#on-the-server)


## Requirements

- a Git platform (like [GitHub](https://github.com/)) account
- a Docker image registry (like [Docker Hub](https://hub.docker.com)) account

## Duplicate the waffle-dashboard to a private repo

First, create a new repo on your Git platform (e.g. [this GitHub UI](https://github.com/new)). Then duplicate the repo like this:

> Where in the following it says `# edit this`: remove that comment and insert the Git repository URL of your private repository in this line.

```shell
git clone --bare https://github.com/lchristmann/waffle-dashboard.git
cd waffle-dashboard.git
git push --mirror https://github.com/lchristmann/custom-waffle-dashboard.git # edit this
cd ..
rm -rf waffle-dashboard.git
```

## Clone the private repo and customize the waffle-dashboard

```shell
git clone https://github.com/lchristmann/custom-waffle-dashboard.git # edit this
cd custom-waffle-dashboard
```

You can now open this folder in your IDE/editor, run the application as described in the [Developer Docs > Setting Up the Development Environment](../DEVELOPER-DOCS.md#setting-up-the-development-environment)
and [Commands for Everyday Development](../DEVELOPER-DOCS.md#commands-for-everyday-development) and perform customizations like the ones I'll present below.

When you're done working commit your changes, by means of your IDE/editor or via CLI:

```shell
git add .
git commit
git push origin main
```

### General Advice

> For customizing the styling, refer to the [Filament 4 > Customizing styling](https://filamentphp.com/docs/4.x/styling/overview) documentation.<br>
> If you can't easily describe what you want to change, the [4_IMPLEMENTATION.md](4_IMPLEMENTATION.md) document provides insight to how I've created the different parts of the application.<br>
> For specific changes like editing a page title, you can search e.g. `page title` in the [Filament 4 docs](https://filamentphp.com/docs/4.x/introduction/overview).


### The App Name

For the app name, you don't even need this whole process. Just edit the `APP_NAME=` variable in the `.env` on your server.
This App Name is shown as the app's logo by default.

### The App's Logo

See the Filament 4 [Customizing styling > Adding a logo](https://filamentphp.com/docs/4.x/styling/overview#adding-a-logo) documentation.

### The Favicon

See the Filament 4 [Customizing styling > Adding a favicon](https://filamentphp.com/docs/4.x/styling/overview#adding-a-favicon) documentation.

### The App's Colors

See the Filament 4 [Customizing styling > Changing the colors](https://filamentphp.com/docs/4.x/styling/overview#changing-the-colors) documentation.

Place to go: [app/Providers/Filament/MainPanelProvider.php](../app/Providers/Filament/MainPanelProvider.php)

### Page Titles

#### Custom Pages (Dashboard, Leaderboard)

Customize the page titles or navigation labels (title in the sidebar) according to the Filament 4 [Custom pages  > Customizing the page title](https://filamentphp.com/docs/4.x/navigation/custom-pages#customizing-the-page-title) and [Customizing the page navigation label](https://filamentphp.com/docs/4.x/navigation/custom-pages#customizing-the-page-navigation-label) documentation.

Places to go: app/Filament/Pages/*.php

#### Resource Pages (Waffles, Users)

Customize the navigation labels according to the Filament 4 [Resources Overview > Resource navigation items](https://filamentphp.com/docs/4.x/resources/overview#resource-navigation-items) documentation
or the page titles by setting a `$title` attribute inside the resource page files.

Places to go:

- app/Filament/Resources/**/*Resource.php
- app/Filament/Resources/**/Pages/*.php

## Pulling Updates from the Public Repository (optional)

If you want to include the latest changes from the public repository in your private fork:

```shell
cd custom-waffle-dashboard
git remote add public https://github.com/lchristmann/waffle-dashboard.git
git pull --no-rebase public main # Creates a merge commit
git push origin main
```

Your private repository now includes the most recent changes from the public repository along with your own modifications.

## Publish and Use your Waffle Dashboard as Private Docker Image

### On your PC

First, create two private repositories (for the two Docker Images built below) in your Docker image registry.

```shell
docker login
# docker login registry.example.com # if you don't use Docker Hub  
```

Set a version that you want to release:

```shell
VERSION=1.0.0
```

Then build, tag and push the docker images:

> Edit the Docker image repository names in the following commands!

```shell
docker build \
  -f ./docker/deployment/php-fpm/Dockerfile \
  -t leanderchristmann/custom-waffle-dashboard:${VERSION} \
  -t leanderchristmann/custom-waffle-dashboard:latest \
  .
```

In the `./docker/deployment/nginx/Dockerfile` change the below line to pull from your previously released custom PHP-FPM waffle-dashboard Image.

```Dockerfile
FROM leanderchristmann/waffle-dashboard:${VERSION:-0.0.0} AS php-fpm-source
# e.g. FROM username/custom-waffle-dashboard:${VERSION:-0.0.0} AS php-fpm-source
```

Now we can build the Nginx Image.

```shell
docker build \
  --build-arg VERSION=${VERSION} \
  -f ./docker/deployment/nginx/Dockerfile \
  -t leanderchristmann/custom-waffle-dashboard-nginx:${VERSION} \
  -t leanderchristmann/custom-waffle-dashboard-nginx:latest \
  .
````

Push both images with their tags:

```shell
docker push leanderchristmann/custom-waffle-dashboard:${VERSION}
docker push leanderchristmann/custom-waffle-dashboard:latest
```

```shell
docker push leanderchristmann/custom-waffle-dashboard-nginx:${VERSION}
docker push leanderchristmann/custom-waffle-dashboard-nginx:latest
```

### On the Server

Set your Docker image repository names and version in the `docker-compose.yaml` on your server:

```shell
cd /opt/waffle-dashboard
nano docker-compose.yaml
```

```yaml
  web:
      image: leanderchristmann/custom-waffle-dashboard-nginx:1.0.0 # <--- here!!

  php-fpm:
    # For the php-fpm service, we will create a custom image to install the necessary PHP extensions and setup proper permissions.
    image: leanderchristmann/custom-waffle-dashboard:1.0.0 # <!--- here
```

Run the below commands to rebuild the waffle-dashboard including your new private custom Docker image.

```shell
docker login -u <your_user> # and enter your password when prompted
docker compose pull
docker compose down
docker compose up -d
```
