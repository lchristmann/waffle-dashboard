# Architecture <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [4. System Decomposition](#4-system-decomposition)
  - [4.1. Approach and Key Architecture Decisions](#41-approach-and-key-architecture-decisions)
- [4.2 Domains](#42-domains)
- [4.3. System Structure](#43-system-structure)
- [4.4. Data Model](#44-data-model)
- [4.5. Code Organization](#45-code-organization)
- [4.6. Build and Deployment](#46-build-and-deployment)
- [4.7. Deployment and Operation](#47-deployment-and-operation)
- [4.8. Technologies](#48-technologies)
- [5. Architecture Concepts](#5-architecture-concepts)
  - [5.1 Usability](#51-usability)
  - [5.2 Portability](#52-portability)
  - [5.3 Aesthetics](#53-aesthetics)
- [6. Risks and Technical Debt](#6-risks-and-technical-debt)
- [7. Outlook and Future Plans](#7-outlook-and-future-plans)

## 4. System Decomposition
 
### 4.1. Approach and Key Architecture Decisions

The Waffle Dashboard is implemented as a **monolithic web application**. A microservices setup was considered unnecessary, since the system scope is small and unlikely to require that level of complexity.

For deployment, the application runs via Docker Compose. The production stack consists of:

- **Nginx** as the web server and reverse proxy,
- **php-fpm** (running a Laravel PHP application) as the runtime,
- **PostgreSQL** as the database (chosen as a reliable default for Laravel).

This setup is self-hosted and can be easily deployed on any server supporting Docker. Scaling is possible by running multiple containers, though for most expected loads, a single stack will suffice.

## 4.2 Domains

The functional domains follow the description in Chapter [2.2. Domain Model](1_REQUIREMENTS.md#22-domain-model).

## 4.3. System Structure

In production, the system is structured as follows:

- **Web layer (Nginx)**: Handles HTTP requests and serves frontend assets.
- **Application layer (Laravel / php-fpm)**: Provides the core waffle tracking logic, dashboard, leaderboard, and admin features.
- **Data layer (PostgreSQL)**: Stores users, waffle entries, and statistics.

Development uses the same structure with one additional workspace container for local development and tooling.

## 4.4. Data Model

Users and waffle entries are stored in PostgreSQL. Admins have full control over user and waffle data.

![Database Schema](diagrams/db-schema.drawio.svg)

The Data model at runtime is self-evident based on the above diagram - it gets mapped to PHP objects by the [Laravel Eloquent ORM](https://laravel.com/docs/12.x/eloquent).

## 4.5. Code Organization

The codebase follows Laravel 12 and Filament 4 conventions.
It is structured as a single monolithic repository without separation of server and client, since the application is server-rendered.

> Since the code organization (after doing [1_REQUIREMENTS.md](1_REQUIREMENTS.md) and [2_DESIGN.md](2_DESIGN.md)) following those conventions is absolutely clear to me - and I'm the only developer - I won't bother sketching it out  in a diagram here.

## 4.6. Build and Deployment

The application is built and deployed via Docker Compose.
Production deployments package the Laravel app, Nginx, and PostgreSQL into containers, ensuring reproducibility. CI/CD can be added on top, but the core process remains straightforward: build the containers and deploy the stack.

## 4.7. Deployment and Operation

![Deployment Diagram](diagrams/deployment-diagram.drawio.svg)

The Waffle Dashboard is provided exclusively as a web application.
It runs in a **Docker Compose environment**, with **containers** for:

- **Laravel / php-fpm**: handles the application logic and server-side rendering.
- **Nginx**: serves frontend assets and acts as a reverse proxy.
- **PostgreSQL**: stores users, waffle entries, and statistics.

This setup makes local development and production deployments **reproducible, lightweight, and easy to manage**. The system is responsive and works on both desktop and mobile browsers. Scaling is possible by running additional containers, though for most expected workloads, a single instance is sufficient.

**Optionally**, an **Nginx Proxy Manager container** can be deployed in front of the application. It provides an easy way to:

- Configure HTTPS via Let’s Encrypt,
- Manage domains and certificates,
- Simplify reverse proxy setup.

While not part of the Waffle Dashboard itself, its use **is recommended for production deployments** to ensure encrypted communication. I have drawn it out here because I will also show it in the setup instructions.

## 4.8. Technologies

- [Laravel 12](https://laravel.com) as the application framework
- [Filament](https://filamentphp.com/) 4 for the server-driven UI (define user interfaces entirely in PHP instead of traditional templating)
- [PostgreSQL](https://www.postgresql.org/) as the database
- [Docker Compose](https://docs.docker.com/compose/) for containerization and deployment

The stack is deliberately simple and follows established conventions, ensuring maintainability and fast onboarding for developers.

## 5. Architecture Concepts

This chapter describes how the functional and quality goals defined by the architecture drivers are addressed.

### 5.1 Usability

**Architecture Driver:** Ensure the platform is intuitive, consistent, and error-resistant (see [3.2.1](1_REQUIREMENTS.md#321-usability)).

**Solution Idea:**

- The system uses Laravel + Filament to provide a server-driven UI, reducing inconsistencies between frontend and backend.
- Form validations, clear error messages, and guided workflows prevent user errors and simplify waffle entry management.
- The dashboard and leaderboard are designed for clarity and immediate comprehension, supporting fast decision-making for users.

**Design Decisions:**

- Choosing a server-rendered UI ensures the interface is always consistent and avoids complex frontend frameworks.
- Use of Filament 4 allows rapid iteration while keeping usability high with prebuilt UI components.

**Discarded Alternatives:**

- Pure frontend frameworks (React/Vue) were considered unnecessary, adding complexity without tangible usability improvements for this project.

### 5.2 Portability

**Architecture Driver:** Enable the system to run reliably in diverse environments with minimal setup (see [3.2.2](1_REQUIREMENTS.md#322-portability)).

**Solution Idea:**

- The system is packaged as a Docker Compose stack, making it portable across Linux servers with minimal setup.
- Optional Nginx Proxy Manager simplifies HTTPS setup and domain configuration without modifying the core application.

**Design Decisions:**

- Self-contained containers ensure the stack runs consistently on any host with Docker.
- No reliance on proprietary cloud services keeps deployment flexible and self-hosted.

**Discarded Alternatives:**

- Deploying only on a specific cloud provider was rejected to maintain portability and independence from cloud-specific tools.
- Manual installation scripts were considered too error-prone and not easily reproducible across environments.

### 5.3 Aesthetics

**Architecture Driver:** Provide a visually appealing interface that enhances enjoyment and engagement (see [3.2.3](1_REQUIREMENTS.md#323-aesthetics)).

**Solution Idea:**

- The dashboard leverages Filament 4 styling conventions to produce a visually clean and consistent interface.
- Clear typography, spacing, and color usage highlight important information like waffle stats and leaderboards.
- UI components maintain a light, friendly, and approachable style to match the playful nature of the Waffle Dashboard.

**Design Decisions:**

- Visual design is maintained through standardized CSS and Filament components, ensuring a polished, coherent look.
- Focus is on clarity and pleasant user experience, rather than heavy custom theming or decorative elements.

**Discarded Alternatives:**

- Fully custom UI frameworks were rejected due to higher maintenance effort and inconsistent look-and-feel.
- Overly flashy or gamified designs were avoided to keep usability and readability as top priorities.

## 6. Risks and Technical Debt

The project shows low technical risk, and no major issues are expected.

## 7. Outlook and Future Plans

The plan is to release the Waffle Dashboard soon (within few weeks) for personal use. This allows me to test it in real situations and see how it performs.

Over the following months, I will make small improvements and fixes based on actual usage. The goal is to have a reliable and enjoyable platform that gradually becomes more mature and polished.

The language and date format settings might be implemented later in the process.