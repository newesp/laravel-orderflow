# Supabase Manual Setup & Configuration Guide

This guide provides step-by-step instructions for configuring **Supabase** to work seamlessly with **OrderFlow Lite (Laravel Admin Panel)** and the shared **Modern Storefront** architecture.

---

## 📑 Table of Contents

1. [Architecture & Ownership Overview](#1-architecture--ownership-overview)
2. [Connection Pooling & IPv4 Configuration (Crucial for Vercel/Serverless)](#2-connection-pooling--ipv4-configuration-crucial-for-vercelserverless)
3. [Supabase Auth & Google OAuth (SSO) Setup](#3-supabase-auth--google-oauth-sso-setup)
4. [Setting Up Administrator Roles (`public.profiles`)](#4-setting-up-administrator-roles-publicprofiles)
5. [Database Schema Initialization (SQL DDL)](#5-database-schema-initialization-sql-ddl)
6. [Storage Buckets Setup](#6-storage-buckets-setup)
7. [Environment Variables Reference](#7-environment-variables-reference)
8. [Common Troubleshooting & FAQ](#8-common-troubleshooting--faq)

---

## 1. Architecture & Ownership Overview

OrderFlow Lite connects directly to the Supabase PostgreSQL database shared with the consumer storefront:

```
+------------------------------------+      +------------------------------------+
|    Modern Storefront (Customer)    |      |    OrderFlow Lite (Admin Panel)    |
|   - React 19 / Supabase Client JS  |      |   - Laravel 13 (PHP 8.3 / PDO)     |
+-----------------+------------------+      +-----------------+------------------+
                  |                                           |
                  | (Supabase SDK / PostgREST)                | (PostgreSQL Direct / Pooler)
                  v                                           v
+--------------------------------------------------------------------------------+
|                         Shared Supabase PostgreSQL DB                          |
|                                                                                |
|  Managed by Storefront:                                                        |
|  - auth.users (Supabase Managed Auth)                                          |
|  - public.profiles (User identity & role)                                      |
|  - public.products (Catalog, prices in NTD integer, text[] image_paths)       |
|  - public.orders (Order header & lifecycle status)                             |
|  - public.order_items (Immutable order item snapshots)                         |
|                                                                                |
|  Managed by Laravel Admin:                                                     |
|  - public.admin_customer_view (Joined presentation view with stats)            |
|  - public.integration_logs (Audit telemetry & webhook dispatched logs)         |
+--------------------------------------------------------------------------------+
```

---

## 2. Connection Pooling & IPv4 Configuration (Crucial for Vercel/Serverless)

> [!IMPORTANT]
> **Why Direct Connection fails on Vercel / AWS Lambda:**
> Supabase direct connections (`db.[project-ref].supabase.co:5432`) use **IPv6-only** addresses by default.
> Serverless hosting environments like **Vercel** only support outbound **IPv4** traffic. Connecting directly causes a `SQLSTATE[08006] [7] Cannot assign requested address` error.
> 
> You **DO NOT** need to pay for a Dedicated IPv4 Add-on. Use the **free Supavisor Shared Connection Pooler (Transaction Mode)** instead.

### Step-by-Step Pooler Setup:

1. Open your [Supabase Dashboard](https://supabase.com/dashboard).
2. Open your project, and click the green **Connect** button in the top right corner.
3. In the connection modal:
   - Select **Transaction pooler** (Port `6543`) or **Session pooler** (Port `5432`).
   - Switch the code tab to **URI** or **PHP / PDO**.
4. Extract your connection parameters from the displayed URI:
   ```
   postgresql://postgres.[PROJECT-REF]:[YOUR-PASSWORD]@aws-0-[REGION].pooler.supabase.com:6543/postgres
   ```
5. Configure these variables in your `.env` (or Vercel Environment Variables):

| Key | Example Value | Description |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `pgsql` | Database driver |
| `DB_HOST` | `aws-0-ap-northeast-1.pooler.supabase.com` | Shared Pooler IPv4 host |
| `DB_PORT` | `6543` | Transaction Pooler port |
| `DB_DATABASE` | `postgres` | Default database name |
| `DB_USERNAME` | `postgres.oxzhpdisyzlftpoxstof` | **Must include `.[PROJECT-REF]` suffix** |
| `DB_PASSWORD` | `[Your-Database-Password]` | The password set during project creation |
| `DB_SSLMODE` | `require` | Required for all Supabase remote connections |

---

## 3. Supabase Auth & Google OAuth (SSO) Setup

OrderFlow Lite allows administrators to log in securely with **Google SSO** through Supabase Auth, validating the resulting JWT signature server-side via Supabase JWKS endpoints.

### Step 1: Create OAuth 2.0 Credentials in Google Cloud Console
1. Go to [Google Cloud Console -> Credentials](https://console.cloud.google.com/apis/credentials).
2. Create an **OAuth client ID** (Application type: *Web application*).
3. Set **Authorized JavaScript origins**:
   - `https://<YOUR-PROJECT-REF>.supabase.co`
4. Set **Authorized redirect URIs**:
   - `https://<YOUR-PROJECT-REF>.supabase.co/auth/v1/callback`
5. Copy the generated **Client ID** and **Client Secret**.

### Step 2: Enable Google Provider in Supabase
1. In the Supabase Dashboard, go to **Authentication** -> **Providers**.
2. Click on **Google** and toggle it **Enabled**.
3. Paste the **Client ID** and **Client Secret** obtained from Google Cloud Console.
4. Click **Save**.

### Step 3: Configure URL Configuration & Redirect URLs
1. In Supabase Dashboard, navigate to **Authentication** -> **URL Configuration**.
2. Set **Site URL**:
   - Production: `https://your-admin-domain.vercel.app`
   - Local: `http://localhost:8000`
3. In **Redirect URLs**, add all valid login callback endpoints:
   - `https://your-admin-domain.vercel.app/admin/login`
   - `http://localhost:8000/admin/login`
   - `http://127.0.0.1:8000/admin/login`
4. Click **Save**.

---

## 4. Setting Up Administrator Roles (`public.profiles`)

OrderFlow Lite enforces role-based access control. When an admin authenticates via Supabase SSO, the server-side `SupabaseJwksService` decodes the token and checks:

$$\text{public.profiles.role} \stackrel{?}{=} \text{'admin'}$$

If the user exists but `role !== 'admin'`, the login is rejected with **HTTP 403 Forbidden**.

### Granting Admin Privileges via SQL:

Open Supabase **SQL Editor** and run the following queries:

```sql
-- 1. Locate the target user ID from auth.users
SELECT id, email, created_at 
FROM auth.users 
WHERE email = 'your-admin-email@gmail.com';

-- 2. Verify their record in public.profiles
SELECT * 
FROM public.profiles 
WHERE id = 'TARGET-USER-UUID';

-- 3. Elevate the user to 'admin'
UPDATE public.profiles 
SET role = 'admin' 
WHERE id = 'TARGET-USER-UUID';

-- (Optional) If the profile record does not exist yet:
INSERT INTO public.profiles (id, display_name, role)
VALUES ('TARGET-USER-UUID', 'Admin User', 'admin')
ON CONFLICT (id) DO UPDATE 
SET role = 'admin';
```

---

## 5. Database Schema Initialization (SQL DDL)

If you are setting up a fresh Supabase database from scratch without the storefront migrations, execute the following SQL scripts in the **SQL Editor**.

### Shared Tables & Schema:

```sql
-- 1. User Profiles (Linked to Supabase Auth)
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    display_name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'customer',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Enable RLS
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;

-- 2. Products Catalog
CREATE TABLE IF NOT EXISTS public.products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT DEFAULT '',
    price INTEGER NOT NULL DEFAULT 0, -- Stored in NTD integer
    image_paths TEXT[] DEFAULT ARRAY[]::TEXT[],
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    is_digital BOOLEAN NOT NULL DEFAULT FALSE,
    digital_file_path VARCHAR(500),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. Orders Header
CREATE TABLE IF NOT EXISTS public.orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE RESTRICT,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    total INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 4. Order Line Items
CREATE TABLE IF NOT EXISTS public.order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id UUID NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
    product_id UUID REFERENCES public.products(id) ON DELETE SET NULL,
    product_name VARCHAR(255) NOT NULL,
    unit_price INTEGER NOT NULL DEFAULT 0,
    quantity INTEGER NOT NULL DEFAULT 1,
    line_total INTEGER GENERATED ALWAYS AS (unit_price * quantity) STORED
);
```

### Laravel Admin Presentation View & Telemetry:

Run these via `php artisan migrate` or manually in SQL Editor:

```sql
-- 5. Customer Aggregation Presentation View
CREATE OR REPLACE VIEW public.admin_customer_view AS
SELECT 
    COALESCE(u.id, p.id) AS id,
    u.email AS email,
    COALESCE(p.display_name, split_part(u.email, '@', 1)) AS display_name,
    COALESCE(p.role, 'customer') AS role,
    COALESCE(p.created_at, u.created_at, NOW()) AS created_at,
    COALESCE(p.updated_at, NOW()) AS updated_at
FROM public.profiles p
FULL OUTER JOIN auth.users u ON u.id = p.id;

-- 6. Integration Telemetry Logs Table
CREATE TABLE IF NOT EXISTS public.integration_logs (
    id BIGSERIAL PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    reference_type VARCHAR(255) NOT NULL,
    reference_id VARCHAR(255) NOT NULL,
    target VARCHAR(255) DEFAULT 'system',
    status VARCHAR(50) DEFAULT 'success',
    payload JSONB,
    response JSONB,
    error_message TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_integration_logs_ref ON public.integration_logs (reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_integration_logs_event ON public.integration_logs (event_type);
```

---

## 6. Storage Buckets Setup

To host product images and digital assets:

1. In Supabase Dashboard, go to **Storage** -> **Buckets**.
2. Create the following buckets:
   - **`product-images`**:
     - Public bucket: **Enabled (Checked)**
     - Used for product cover and display images.
   - **`product-files`**:
     - Public bucket: **Disabled (Unchecked)** or Enabled depending on your distribution model.
     - Used for downloadable digital product files (`.pdf`, `.zip`, `.rar`).
3. **Storage Access Policies & Service Role Key**:
   - OrderFlow Lite's Laravel backend proxies image and digital file uploads directly to Supabase Storage.
   - Setting the **`SUPABASE_SERVICE_ROLE_KEY`** in your environment variables allows the backend to bypass Row-Level Security (RLS) policies and upload files smoothly for both formal administrators and demo evaluation sessions.

---

## 7. Environment Variables Reference

When deploying OrderFlow Lite (e.g. on Vercel), configure the following environment variables:

```ini
APP_NAME="OrderFlow Lite"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY
APP_DEBUG=false
APP_URL=https://your-domain.vercel.app

# Serverless Session & Cache Strategy
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
CACHE_STORE=array
LOG_CHANNEL=stderr

# Supabase PostgreSQL Pooler Configuration (IPv4)
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_REF
DB_PASSWORD=YOUR_DB_PASSWORD
DB_SSLMODE=require

# Supabase Configuration (JWKS SSO & Storage Uploads)
SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co
SUPABASE_ANON_KEY=YOUR_SUPABASE_ANON_KEY
SUPABASE_SERVICE_ROLE_KEY=YOUR_SUPABASE_SERVICE_ROLE_KEY # Required for backend Storage uploads

# Demo Admin Evaluation Mode (Optional)
DEMO_ADMIN_ENABLED=true
DEMO_ADMIN_EMAIL=demo@example.com
DEMO_ADMIN_PASSWORD=your_demo_password

# Webhook Telemetry Target (Optional)
DEMO_WEBHOOK_URL=https://webhook.site/your-unique-uuid
```

---

## 8. Common Troubleshooting & FAQ

### Q1: `SQLSTATE[08006] [7] Cannot assign requested address`
*   **Cause**: The application is attempting to connect to `db.[ref].supabase.co` via IPv6 from a platform (like Vercel) that only supports IPv4.
*   **Solution**: Switch `DB_HOST` to the **Transaction Pooler** endpoint (`aws-0-[region].pooler.supabase.com`), set `DB_PORT=6543`, and append `.[PROJECT-REF]` to `DB_USERNAME`.

### Q2: Supabase Google SSO gives `403 Forbidden: Formal administrator privileges required`
*   **Cause**: The user logged in via Google SSO successfully, but their `role` in the `public.profiles` table is `customer` or null.
*   **Solution**: Run `UPDATE public.profiles SET role = 'admin' WHERE id = '<user-uuid>';` in Supabase SQL Editor.

### Q3: `500 Server Error` when visiting `/admin/customers` or `/admin/integration-logs`
*   **Cause**: The custom SQL view `admin_customer_view` or table `integration_logs` has not been migrated on the Supabase database.
*   **Solution**: Run `php artisan migrate` locally against Supabase.

### Q4: Google OAuth redirects to an error or wrong URL
*   **Cause**: The redirect URL is not listed under **Authentication -> URL Configuration -> Redirect URLs**.
*   **Solution**: Add your exact domain callback (e.g. `https://your-domain.vercel.app/admin/login`) to the Redirect URLs list.

### Q5: Failed to upload image/file: `403 Forbidden: new row violates row-level security policy`
*   **Cause**: The upload request failed Supabase Storage Row-Level Security (RLS) policies because it used the anonymous key or lacked an admin JWT.
*   **Solution**: Add `SUPABASE_SERVICE_ROLE_KEY` to your local `.env` and Vercel Environment Variables. The backend will automatically use the Service Role Key to bypass RLS for administrative uploads.

