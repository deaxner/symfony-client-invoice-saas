# Symfony Client Invoice SaaS

Small SaaS-style web application for managing clients and invoices with Symfony,
Twig, Doctrine ORM, and MySQL.

## Features

- Session-based authentication with register, login, and logout
- CRUD for clients
- CRUD for invoices
- Paid and unpaid invoice status
- Dashboard with client, invoice, and revenue stats
- Service-layer business logic with repositories and DTO-backed forms
- CSRF protection, password hashing, and validation
- Bonus REST API for clients and invoices

## Stack

- Symfony 7.4
- PHP 8.2+
- Twig
- Doctrine ORM
- MySQL 8

## Folder Structure

```text
symfony-client-invoice-saas/
├── assets/
│   ├── styles/
│   └── app.js
├── bin/
├── config/
├── migrations/
├── public/
│   ├── index.php
│   └── styles/app.css
├── src/
│   ├── Controller/
│   │   ├── Api/
│   │   ├── AuthController.php
│   │   ├── ClientController.php
│   │   ├── DashboardController.php
│   │   └── InvoiceController.php
│   ├── DTO/
│   ├── Entity/
│   ├── EventSubscriber/
│   ├── Form/
│   ├── Repository/
│   ├── Security/
│   └── Service/
├── templates/
│   ├── auth/
│   ├── client/
│   ├── dashboard/
│   └── invoice/
├── tests/
├── .env
└── README.md
```

## Key Files

- `src/Controller/AuthController.php`: login, register, logout routes
- `src/Controller/DashboardController.php`: dashboard stats view
- `src/Controller/ClientController.php`: HTML CRUD for clients
- `src/Controller/InvoiceController.php`: HTML CRUD for invoices
- `src/Controller/Api/ClientApiController.php`: JSON client endpoints
- `src/Controller/Api/InvoiceApiController.php`: JSON invoice endpoints
- `src/Entity/User.php`, `src/Entity/Client.php`, `src/Entity/Invoice.php`: core domain entities
- `src/Service/ClientService.php`, `src/Service/InvoiceService.php`, `src/Service/DashboardService.php`: business logic
- `src/Security/LoginFormAuthenticator.php`: session login flow
- `src/EventSubscriber/ApiExceptionSubscriber.php`: JSON API error handling

## Example Routes

- `GET|POST /login`
- `GET|POST /register`
- `GET /`
- `GET /clients`
- `GET|POST /clients/new`
- `GET|POST /clients/{id}/edit`
- `GET /invoices`
- `GET|POST /invoices/new`
- `GET|POST /invoices/{id}/edit`
- `GET /api/clients`
- `POST /api/clients`
- `GET /api/invoices`
- `POST /api/invoices`

## Setup

1. Install dependencies:

```bash
composer install
```

2. Configure your database in `.env` or `.env.local`:

```dotenv
DATABASE_URL="mysql://app:password@127.0.0.1:3306/client_invoice_saas?serverVersion=8.0.32&charset=utf8mb4"
```

3. Create the database and run migrations:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

4. Run the app locally:

```bash
symfony server:start
```

5. Run tests:

```bash
php bin/phpunit
```

## Local MySQL with Docker

This repo includes a full Docker Compose setup for the Symfony app and MySQL.

Start the full stack on Windows:

```powershell
./docker-up.ps1
```

This prefers app port `8085` and MySQL port `3308`, then falls back if either
port is in use.

The app runs inside Docker and connects to MySQL through the `database` service.
The database is also exposed on the selected host port for local inspection.

If you want local CLI access outside Docker, point `.env.local` to the selected
MySQL port, for example:

```dotenv
DATABASE_URL="mysql://app:ChangeMe123!@127.0.0.1:3308/client_invoice_saas?serverVersion=8.0.32&charset=utf8mb4"
```

Apply migrations inside the app container:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Seed demo data inside the app container:

```bash
docker compose exec app php bin/console app:seed-demo-data
```

Demo credentials:

- `owner@example.com / Password123`
- `finance@example.com / Password123`

The demo seed creates 2 users, 6 clients, and 24 invoices and is safe to rerun.

## API Notes

- API endpoints live under `/api`
- They return JSON payloads for the authenticated user
- Session authentication is used by default in this implementation

## Next Steps

- Add invoice PDF export
- Add email sending for invoices
- Add pagination and filtering to list views
- Add token-based API authentication if external apps need non-browser access
