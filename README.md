# OrderFlow Lite — Modern Storefront Laravel Admin Backend

[![Laravel CI](https://github.com/newesp/laravel-orderflow/actions/workflows/ci.yml/badge.svg)](https://github.com/newesp/laravel-orderflow/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2F12-FF2D20?logo=laravel)](https://laravel.com/)
[![Deployment: Vercel](https://img.shields.io/badge/Deploy-Vercel%20Serverless-000000?logo=vercel)](https://vercel.com/)

An enterprise-grade, high-performance Order & Catalog Management backend and administrative system designed to integrate directly with [Modern Storefront](https://github.com/newesp/modern-storefront) over a shared **Supabase PostgreSQL** database architecture.

---

## 📑 Table of Contents

- [Architectural Overview](#-architectural-overview)
- [Core Features](#-core-features)
- [Authentication & Security Architecture](#-authentication--security-architecture)
- [Database Ownership Boundary](#-database-ownership-boundary)
- [Quick Start (Local Development)](#-quick-start-local-development)
- [Vercel Serverless Deployment](#-vercel-serverless-deployment)
- [Supabase Manual Setup Guide](#-supabase-manual-setup-guide)
- [Automated Testing & CI](#-automated-testing--ci)
- [REST API Reference](#-rest-api-reference)
- [License](#-license)

---

## 🌟 Architectural Overview

```
+-------------------------------------------------------------------------------+
|                       Modern Storefront (React 19 / Vite)                     |
|                                                                               |
|  - Customer Browse & Cart                                                     |
|  - Checkout RPC (create_demo_order)                                           |
|  - Supabase Auth (Google SSO / Email) & Storage                               |
+---------------------------------------+---------------------------------------+
                                        | (Shared PostgreSQL)
                                        v
+-------------------------------------------------------------------------------+
|                         Shared Supabase PostgreSQL DB                         |
|                                                                               |
|  - products (UUID, text[] image_paths, is_digital, price NTD integer)         |
|  - orders & order_items (UUID PK, line_total generated stored)                |
|  - profiles (display_name, role) & auth.users                                 |
|  - public.admin_customer_view (Read-only joined presentation view)            |
|  - public.integration_logs (Audit telemetry & webhook event records)          |
+-------------------------------------------------------------------------------+
                                        ^
                                        | (Direct PDO / Supavisor IPv4 Pooler)
+---------------------------------------+---------------------------------------+
|                    OrderFlow Lite (Laravel Admin Panel)                       |
|                                                                               |
|  - Executive Dashboard & Metric Aggregations                                  |
|  - Product Catalog CRUD & Active Toggling                                     |
|  - Customer Lifetime Value & Order History                                    |
|  - Order Operations & Directional State Machine                               |
|  - Integration Telemetry & Resilient Webhook Pipeline                         |
|  - Admin Auth: Supabase Google SSO (JWKS) + Env-based Demo Admin              |
|  - Blade SSR + Modern Tailwind UI + RESTful JSON APIs                         |
+-------------------------------------------------------------------------------+
```

---

## ✨ Core Features

1. **Executive Dashboard & Real-Time Metrics**:
   - Total Gross Revenue, Pending Orders count, Active Products count, and Total Customers.
   - Order pipeline breakdown (`pending`, `processing`, `completed`, `cancelled`).
   - Recent orders quick-view with direct navigation to fulfillment operations.

2. **Product Catalog Management**:
   - Full CRUD operations supporting digital assets (`is_digital`, `digital_file_path`) and multiple image paths (`text[]`).
   - Price management in NTD integer format.
   - Quick one-click active/inactive visibility toggling.

3. **Customer Intelligence & Lifetime Value (LTV)**:
   - Aggregated presentation view (`admin_customer_view`) joining `auth.users` with `public.profiles`.
   - Comprehensive customer profile showing total spend, order count, and complete order history.

4. **Order Operations & Directional State Machine**:
   - Strict transition rules (`pending -> processing -> completed` / `cancelled`).
   - Prevents illegal rollback from terminal states.
   - Order line item snapshots with unit prices and computed totals.

5. **Integration Telemetry & Webhook Pipeline**:
   - Internal audit trail recording every order state change.
   - Non-blocking webhook dispatcher forwarding events to third-party endpoints (e.g. logistics, Slack).
   - Full request and response payload logging for auditability and debugging.

---

## 🔐 Authentication & Security Architecture

OrderFlow Lite implements a strict, modern security architecture:

### 1. Formal Administrator (Supabase SSO)
- Authenticated via **Supabase Auth (Google SSO)**.
- Validated server-side using **Supabase JWKS public keys** (`{SUPABASE_URL}/auth/v1/.well-known/jwks.json`).
- Cached public keyset with automatic refresh via Laravel Cache.
- Verifies JWT signature, expiration (`exp`), issuer (`iss`), and audience (`aud`).
- Extracts `sub` claim and verifies that `public.profiles.role === 'admin'`.
- Session-backed identity via `AdminSessionUser` (`is_demo = false`).
- **Zero database passwords or Eloquent persistence required for admin identity**.

### 2. Demo Administrator (Evaluation Mode)
- Configured strictly via server-side environment variables:
  ```env
  DEMO_ADMIN_ENABLED=true
  DEMO_ADMIN_EMAIL=demo@example.com
  DEMO_ADMIN_PASSWORD=your-secret-password
  ```
- Evaluates credentials using timing-attack safe comparisons (`hash_equals`).
- Session-backed identity via `AdminSessionUser` (`is_demo = true`).
- **Non-persistent** (no database user records created).
- **Destructive Operation Guards**: Demo administrators are barred from hard-deleting catalog products or altering system secrets.

---

## 📦 Database Ownership Boundary

| Object | Managing Authority | Laravel Migration Behavior |
| :--- | :--- | :--- |
| `auth.users` | Supabase Auth | Read-only join |
| `public.profiles` | Storefront Supabase Migrations | Read-only join / role verification |
| `public.products` | Storefront Supabase Migrations | Managed via Eloquent Model |
| `public.orders` | Storefront Supabase Migrations | Lifecycle progressed via State Machine |
| `public.order_items` | Storefront Supabase Migrations | Read-only immutable snapshot |
| `public.admin_customer_view` | **Laravel Migration** | Created as presentation view |
| `public.integration_logs` | **Laravel Migration** | Created as telemetry audit table |

*Note: Laravel production migrations never create, modify ownership of, or drop Storefront shared tables.*

---

## 🚀 Quick Start (Local Development)

### Prerequisites
- PHP 8.3+ (with `pdo_pgsql`, `pgsql`, `mbstring`, `fileinfo`, `zip`, `openssl`)
- Composer 2.x
- Node.js 20+ & npm

### Setup Steps

1. **Clone the repository**:
   ```bash
   git clone https://github.com/newesp/laravel-orderflow.git
   cd laravel-orderflow
   ```

2. **Install Dependencies & Build Assets**:
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Fill in your Supabase connection parameters in `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
   DB_PORT=6543
   DB_DATABASE=postgres
   DB_USERNAME=postgres.YOUR_PROJECT_REF
   DB_PASSWORD=your-database-password
   DB_SSLMODE=require

   SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co
   SUPABASE_ANON_KEY=your-anon-key

   DEMO_ADMIN_ENABLED=true
   DEMO_ADMIN_EMAIL=demo@example.com
   DEMO_ADMIN_PASSWORD=demo1234
   ```

4. **Run Laravel Migrations**:
   ```bash
   php artisan migrate
   ```
   *(Only creates `admin_customer_view` and `integration_logs`)*.

5. **Start Local Server**:
   ```bash
   php artisan serve
   ```
   Navigate to [http://localhost:8000/admin](http://localhost:8000/admin).

---

## ☁️ Vercel Serverless Deployment

OrderFlow Lite is optimized for deployment on **Vercel Serverless** using `vercel-php@0.7.3`.

### Key Serverless Adaptations:
- **Ephemeral Storage**: In `api/index.php`, all compiled views, cache, and session paths are bound to `/tmp/storage`.
- **Session Strategy**: Uses encrypted client `cookie` sessions (`SESSION_DRIVER=cookie`) so logins persist seamlessly across serverless Lambda invocations.
- **Cache Strategy**: Uses in-memory `array` cache or Redis for serverless execution.
- **Connection Pooling (IPv4)**: Vercel serverless functions do not support outbound IPv6 direct connections. Set `DB_HOST` to the Supabase **Transaction Pooler** (`aws-0-*.pooler.supabase.com`) on Port `6543`.

### Required Vercel Environment Variables:
```env
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
SESSION_DRIVER=cookie
CACHE_STORE=array
LOG_CHANNEL=stderr

DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_REF
DB_PASSWORD=YOUR_DB_PASSWORD
DB_SSLMODE=require

SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co
SUPABASE_ANON_KEY=YOUR_SUPABASE_ANON_KEY

DEMO_ADMIN_ENABLED=true
DEMO_ADMIN_EMAIL=demo@example.com
DEMO_ADMIN_PASSWORD=demo1234
```

---

## 📖 Supabase Manual Setup Guide

For detailed step-by-step instructions on setting up Supabase, Google OAuth SSO, database tables, connection pooler, and admin role assignments, see our dedicated guide:

👉 [**Supabase Manual Setup Guide (docs/supabase-setup.md)**](docs/supabase-setup.md)

---

## 🧪 Automated Testing & CI

Automated tests run on every push and pull request via GitHub Actions against an isolated **PostgreSQL 16** service container.

```bash
# Execute test suite locally
vendor/bin/phpunit
```

### Verified Test Matrix (40 Tests, 126 Assertions)
- **Migration Ownership Audit**: Confirms rollback does not drop shared Storefront tables.
- **Supabase JWKS SSO Validation**: Verifies valid admin token authorization, token claims (`iss`, `aud`, `exp`), and non-admin 403 rejection.
- **Env-based Demo Admin**: Verifies login, disabled states, session invalidation, and destructive operation guards.
- **State Machine Progression**: Verifies directional transitions (`pending -> processing/completed`) and illegal transition HTTP 422 mapping.
- **Integration Telemetry**: Verifies event logging and non-blocking webhook error handling.

---

## 📡 REST API Reference

All administrative API endpoints are prefixed with `/api/admin` and protected by the `auth:admin` guard.

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/admin/login` | Authenticate via demo credentials or Supabase `access_token` |
| `POST` | `/api/admin/logout` | Invalidate admin session |
| `GET` | `/api/admin/me` | Current authenticated admin profile |
| `GET` | `/api/admin/products` | Paginated product list with search/filters |
| `POST` | `/api/admin/products` | Create a new catalog product |
| `GET` | `/api/admin/products/{id}` | Get product details |
| `PUT` | `/api/admin/products/{id}` | Update product details |
| `PATCH` | `/api/admin/products/{id}/status` | Toggle product active status |
| `DELETE` | `/api/admin/products/{id}` | Delete product *(blocked for Demo Admin)* |
| `GET` | `/api/admin/customers` | Paginated customer list with order stats |
| `GET` | `/api/admin/customers/{id}` | Customer profile & order history |
| `GET` | `/api/admin/orders` | Paginated orders list with status filters |
| `GET` | `/api/admin/orders/{id}` | Order snapshot details and line items |
| `PATCH` | `/api/admin/orders/{id}/status` | Transition order lifecycle status |
| `GET` | `/api/admin/integration-logs` | Telemetry audit trail and webhook logs |

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
