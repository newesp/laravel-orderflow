# SPEC-001: OrderFlow Lite — Laravel Admin Backend for Modern Storefront

> GitHub Issue: [#1](https://github.com/newesp/laravel-orderflow/issues/1)  
> Status: `ready-for-agent`  
> Domain Context: [`CONTEXT.md`](../CONTEXT.md)

---

## Problem Statement

Small e-commerce businesses operating with `modern-storefront` need a performant, reliable, and secure back-office web administration system to manage product listings, inspect customer profiles, audit orders, transition order lifecycle statuses, and monitor integration telemetry.

Currently, administrative tasks in `modern-storefront` are partially handled via client-side React routes interacting directly with Supabase RLS. However, a dedicated server-side SaaS backend (built with PHP 8.3+ and Laravel 13) is required to provide server-side business workflows, robust data aggregation (such as customer lifetime value and order metrics), deterministic state machine transitions with business rule validation, demo login capabilities for prospective employers/evaluators, and integration webhook telemetry—all while safely sharing the existing Supabase PostgreSQL database without degrading storefront operations, changing storefront semantics, or exposing privileged credentials.

---

## Solution

Build **OrderFlow Lite** — a PHP 8.3+ / Laravel SaaS Administration Backend connecting directly to the shared Supabase PostgreSQL database via a secure server-side connection.

Key solution elements:
1. **Shared Database Architecture**: Reads from and writes to existing PostgreSQL tables (`products`, `orders`, `order_items`, `profiles`, `auth.users`) without breaking existing RLS, triggers, or the `create_demo_order` RPC.
2. **Dedicated Admin Authentication**: Implements a session-backed `AdminSessionUser` leveraging Supabase SSO (JWT/JWKS) for formal administrators and server-side environment variables for Demo Account capabilities.
3. **Customer Presentation View**: Utilizes a secure PostgreSQL view `public.admin_customer_view` that safely joins `auth.users` with `public.profiles` to expose customer identity, ordering history, and lifetime spending without creating redundant customer records.
4. **Product Catalog Management**: Manages physical and digital products respecting `slug` uniqueness, PostgreSQL `text[]` image paths, and `is_digital` / `digital_file_path` private storage attributes.
5. **Deterministic Order Status Lifecycle**: Implements a strict `OrderStatusService` that manages strict status transitions (`pending -> processing | cancelled`, `processing -> cancelled`, `received -> completed`) and rejects illegal transitions by throwing domain business exceptions (`InvalidOrderStatusTransitionException`), which the API layer maps to HTTP 422 responses.
6. **Integration Telemetry**: Records domain events and webhook dispatches in `public.integration_logs` with resilient offline mocking in CI.

---

## User Stories

### Admin Authentication & Demo Access
1. As an administrator, I want to log in using my email and password on a dedicated Laravel login page, so that I can access the administrative dashboard.
2. As an administrator, I want to log out securely, so that unauthorized users cannot access the administration console from my device.
3. As a portfolio evaluator, I want to log in with a pre-configured demo account (`demo@example.com` / `demo1234`) with one click or standard input, so that I can immediately evaluate the system without registering.
4. As a guest or unauthenticated user, I want any attempt to access protected `/admin/*` routes or `/api/admin/*` endpoints to be rejected with a redirect or 401 Unauthorized response, so that system data remains protected.
5. As a system operator, I want demo administrator credentials to be protected against modification or deletion, so that the public demo remains permanently available.

### Dashboard & Analytics
6. As an administrator, I want to view high-level metric cards on the Dashboard (Total Products, Active Products, Total Customers, Pending Orders, Processing Orders, Completed Orders, Total Revenue), so that I can immediately understand business health.
7. As an administrator, I want to view a list of the most recent orders on the Dashboard, so that I can quickly act on newly placed customer purchases.
8. As an administrator, I want monetary metrics to be formatted consistently in integer NTD (e.g. `NT$ 12,500`), so that financial summaries match storefront pricing.

### Customer Management & Insights
9. As an administrator, I want to browse a paginated list of customers with their display names, email addresses, registration dates, total order counts, and total spending, so that I can assess customer activity.
10. As an administrator, I want to search customers by email or display name, so that I can quickly locate specific customer accounts.
11. As an administrator, I want to view a customer's detailed profile, including their full order history, so that I can provide customer support.

### Product Catalog Management
12. As an administrator, I want to browse a paginated list of all products with their thumbnail, name, slug, price, featured badge, digital status, and active toggle, so that I have a complete overview of the catalog.
13. As an administrator, I want to search products by name or slug, and filter by status (All, Active, Inactive, Featured), so that I can easily find products.
14. As an administrator, I want to create a new product by specifying name, unique slug (with auto-generation from name), integer price (>= 0), description, featured flag, active flag, image URLs/paths, and digital product settings, so that new inventory is made available to storefront customers.
15. As an administrator, I want to edit an existing product's details and have updates immediately reflected in the database, so that storefront customers see accurate information.
16. As an administrator, I want to toggle a product's active status directly from the product list or edit form, so that discontinued items are instantly hidden from storefront browsing.
17. As an administrator, I want validation errors (e.g. empty name, negative price, duplicate slug) to be displayed clearly with field-specific feedback, so that invalid data is never committed.

### Order Operations & State Machine
18. As an administrator, I want to browse a paginated list of all orders showing Order UUID, customer display name/email, order status badge, item count, total amount, and placement timestamp, so that I can oversee order fulfillment.
19. As an administrator, I want to filter orders by status (`pending`, `processing`, `completed`, `cancelled`) and search by Order ID or customer email, so that I can focus on actionable orders.
20. As an administrator, I want to view full order details showing immutable order items (snapshot product name, snapshot unit price, quantity, and line total), so that I have an audit-proof record of what was purchased.
21. As an administrator, I want to distinguish between physical orders (which start as `pending`) and digital orders (which start as `completed`), so that physical fulfillment workflows are only applied to shippable goods.
22. As an administrator, I want to transition an order from `pending` to `processing`, so that warehouse staff can prepare physical shipments.
23. As an administrator, I want to transition an order from `processing` to `completed`, so that the customer is notified of order fulfillment.
24. As an administrator, I want to cancel an order in `pending` or `processing` state, so that invalid or refunded orders are properly terminated.
25. As an administrator, I want any illegal state transition (e.g. `completed -> pending`, `cancelled -> processing`) to be rejected with an explicit business error and HTTP 422 response, so that order integrity is strictly maintained.

### Integration Telemetry & Webhooks
26. As an administrator, I want to view an Integration Logs page detailing recent system events (event type, target, reference ID, status, payload, error message, timestamp), so that I can monitor outbound integrations.
27. As a developer/integrator, I want order status change events (`order.status_changed`) to automatically create an integration log entry, so that background operations are traceable.
28. As a developer/integrator, I want webhook dispatch failures (such as endpoint timeouts) to be logged as `failed` without rolling back successful order status updates, so that system resilience is preserved.

### REST API Consumers
29. As an API client, I want JSON endpoints (`/api/admin/products`, `/api/admin/orders`, `/api/admin/customers`, `/api/admin/integration-logs`) to return uniform, predictable JSON structures (`{ "success": true, "data": ... }`), so that programmatic integrations are straightforward.
30. As an API client, I want API validation failures and business transition errors to return standardized `{ "success": false, "message": "...", "errors": { ... } }` payloads with HTTP 422 status codes, so that client applications can display meaningful feedback.

---

## Implementation Decisions

### 1. Architectural Boundaries & Domain Alignment
- **Co-existence with Storefront**: `modern-storefront` retains full ownership of customer browsing, Google OAuth authentication, Cart management, and the `create_demo_order` RPC checkout.
- **Backend Responsibility**: Laravel Admin acts as the operational back-office, handling administrator authentication, catalog maintenance, customer analytics, order status progression, and integration logging.
- **No Shared Secrets in Frontend**: Database credentials remain strictly confined to server-side `.env` configuration.

### 2. Database Schema, Models & Views
- **Shared Schema Entities**:
  - `public.products`: Primary key `id` (UUID), `price` (integer), `image_paths` (text[]), `slug` (text unique), `is_digital` (boolean), `digital_file_path` (text nullable).
  - `public.orders`: Primary key `id` (UUID), `user_id` (UUID -> `auth.users`), `total` (integer), `status` (text check constraint).
  - `public.order_items`: Primary key `id` (BIGSERIAL / bigint identity), `order_id` (UUID), `product_id` (UUID nullable), `product_name` (text snapshot), `unit_price` (integer snapshot), `quantity` (integer), `line_total` (stored generated column).
  - `public.profiles`: Primary key `id` (UUID -> `auth.users`), `display_name` (text), `role` (text).
  - `auth.users`: Supabase Auth identity table (`id`, `email`, `created_at`).
- **Laravel-Managed Migrations**:
  - `public.integration_logs`: `id` (bigint PK), `event_type` (string), `reference_type` (string), `reference_id` (string), `target` (string), `status` (string), `payload` (jsonb nullable), `response` (jsonb nullable), `error_message` (text nullable), `created_at` (timestamp).
  - `public.admin_customer_view`: PostgreSQL View definition:
    ```sql
    CREATE OR REPLACE VIEW public.admin_customer_view AS
    SELECT 
      u.id AS id,
      u.email AS email,
      u.created_at AS created_at,
      p.display_name AS display_name,
      p.role AS role
    FROM auth.users u
    LEFT JOIN public.profiles p ON p.id = u.id;
    ```
- **Eloquent Configuration for UUIDs & Constraints**:
  - Models for `Product`, `Order`, `Profile`, and `Customer` must configure `$incrementing = false` and `protected $keyType = 'string'`.
  - `OrderItem` model must guard `line_total` against write operations (since it is a PostgreSQL stored generated column).
  - Money attributes must cast to integer, never float or decimal.

### 3. Order Status State Machine & Exception Handling
- **Permitted Transitions**:
  ```text
  pending    -> processing | cancelled
  processing -> cancelled
  received   -> completed
  completed  -> none
  cancelled  -> none
  ```
  *(Note: `processing -> received` 不是 Laravel Admin action；Laravel 只會讀到 `received`，並允許 `received -> completed`。)*
- **Service Layer & Domain Exceptions**:
  - Transition logic is encapsulated inside `App\Services\OrderStatusService`.
  - Illegal transitions throw a domain business exception: `App\Exceptions\InvalidOrderStatusTransitionException`.
  - The application exception handler / API controller layer catches `InvalidOrderStatusTransitionException` and transforms it into an HTTP 422 Unprocessable Entity response with `{ "success": false, "message": "..." }`.
  - Blade controllers catch the exception to return user-friendly flash warning notifications.

### 4. Integration Telemetry & Event Pipeline
- Order status changes and product updates dispatch internal Laravel events (`App\Events\OrderStatusChanged` and `App\Events\ProductUpdated`).
- Event listeners invoke `App\Services\IntegrationService` to write internal audit records to `public.integration_logs`.
- For order status changes, if `DEMO_WEBHOOK_URL` is populated in the environment, the integration service additionally dispatches an asynchronous/synchronous HTTP POST request with a timeout. If the request fails, the integration log status is recorded as `failed` with the error trace, while preserving the committed database transaction. (Product updates only target internal-audit).

---

## Testing Decisions

### 1. Seam Architecture
- **Primary Seam — HTTP Feature Tests (Highest Seam)**:
  - Feature tests simulate authentic browser sessions and JSON REST API requests using `$this->actingAs($adminUser)`.
  - Tests verify end-to-end controller response, database state assertions, and session/JSON response formatting.
- **Service Seam — Order Status State Machine Tests**:
  - Unit/Service tests verify all permutation branches of status transitions and assert that `InvalidOrderStatusTransitionException` is thrown on invalid state jumps.

### 2. Test Isolation & Prior Art
- **Database Isolation**:
  - Tests run against a dedicated test database (PostgreSQL container in CI or local test DB) using `RefreshDatabase`.
  - Production Supabase credentials are never invoked during automated tests.
- **External Network Isolation**:
  - External webhook dispatches are intercepted using `Http::fake()` to ensure 100% deterministic, offline-capable test execution.

### 3. Key Required Test Suites
- **Auth Tests**: Guest redirection, admin login success/failure, demo admin account protection.
- **Product Tests**: Product creation with validation, unique slug enforcement, active state toggling, digital file path persistence.
- **Customer Tests**: Querying customers via `admin_customer_view`, order aggregation calculation.
- **Order Tests**: Order filtering, detail inspection, valid transition progression (`pending -> processing`, `received -> completed`).
- **State Machine Boundary Tests**: Asserting HTTP 422 / exception on illegal transitions (`completed -> pending`, `cancelled -> processing`).
- **Integration Log Tests**: Ensuring log generation upon order state changes and asserting graceful failure recording when webhook fails.

---

## Out of Scope

- **Customer-Facing Checkout**: Handled exclusively by `modern-storefront` and PostgreSQL RPC `create_demo_order`.
- **Payment Processing Gateways**: No live credit card / third-party payment gateway integration.
- **Inventory Deduction / Warehouse Tracking**: Infinite demo inventory model preserved per ADR-0004.
- **Direct Auth Mutation**: Modifying customer passwords or altering `auth.users` authentication credentials directly from the admin panel.
- **Complex Multi-Tenancy or RBAC**: Single admin role tier with Demo access.

---

## Further Notes

- **Ubiquitous Language**: Aligned with `CONTEXT.md` (`Order`, `OrderItem`, `Product`, `Customer`, `AdminUser`, `IntegrationLog`).
- **Demo Account Configuration**: Pre-seeded with `demo@example.com` / `demo1234` for rapid evaluation.
- **Shared Database Compatibility**: Retains 100% backward and forward compatibility with `modern-storefront` web client and database migrations.
