# OrderFlow Lite — Modern Storefront Laravel Admin Backend

[![Laravel CI](https://github.com/newesp/laravel-orderflow/actions/workflows/ci.yml/badge.svg)](https://github.com/newesp/laravel-orderflow/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2F12-FF2D20?logo=laravel)](https://laravel.com/)

An enterprise-grade, high-performance Order & Catalog Management backend and administrative system designed to integrate directly with [Modern Storefront](https://github.com/newesp/modern-storefront) over a shared **Supabase PostgreSQL** database architecture.

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
|  - public.integration_logs (Audit telemetry)                                  |
+---------------------------------------+---------------------------------------+
                                        ^
                                        | (Direct PDO / Eloquent)
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

## 🔐 Authentication & Authorization Model

OrderFlow Lite implements a strict, modern security architecture:

### 1. Formal Administrator (Supabase SSO)
- Authenticated via **Supabase Auth (Google SSO)**.
- Validated server-side using **Supabase JWKS public keys** (`{SUPABASE_URL}/auth/v1/.well-known/jwks.json`).
- Verifies JWT signature, expiration, issuer, and audience.
- Extracts `sub` claim and queries `public.profiles` to ensure `role === 'admin'`.
- Session-backed identity via `AdminSessionUser` (`is_demo = false`).
- **No passwords stored in the application database**.

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

## 🚀 Installation & Setup

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
   DB_HOST=db.your-supabase-id.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=your-database-password
   DB_SSLMODE=require

   SUPABASE_URL=https://your-supabase-id.supabase.co
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

## 🧪 Automated Testing & CI

Automated tests run on every push and pull request via GitHub Actions against an isolated **PostgreSQL 16** service container.

```bash
# Execute test suite
vendor/bin/phpunit
```

### Verified Test Matrix (38 Tests, 123 Assertions)
- **Migration Ownership Audit**: Confirms rollback does not drop shared Storefront tables.
- **Supabase JWKS SSO Validation**: Verifies valid admin token authorization and non-admin 403 rejection.
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
