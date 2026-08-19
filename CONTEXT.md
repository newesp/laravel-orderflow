# OrderFlow Lite Context

A lightweight order management SaaS backend for small businesses, co-existing with `modern-storefront` on a shared Supabase PostgreSQL database to manage products, customers, orders, status workflows, and external integration events.

## Language

### Core Entities

**Order**:
A formal commercial purchase record submitted by a customer via the Storefront RPC and managed by administrators in the backend, identified by a UUID primary key, with total stored as integer NTD and an explicit lifecycle status.
_Avoid_: Purchase, transaction, bill, cart

**OrderItem**:
An immutable historical snapshot of a purchased product, capturing the unit price (integer NTD), product name, quantity, and generated line total at the moment of order creation.
_Avoid_: Line item, cart item, order product

**Product**:
A sellable commodity identified by a unique `slug`, integer NTD pricing, image paths array, active visibility state, featured flag, and optional digital product file attributes (`is_digital`, `digital_file_path`).
_Avoid_: Merchandise, good, catalog item, SKU

**Customer**:
A read-only presentation concept aggregated from `auth.users` and `public.profiles` via `public.admin_customer_view`, linked to orders and cumulative purchase history via `orders.user_id`.
_Avoid_: Client, buyer, account, user

**AdminUser**:
A dedicated backend administrative user account in `public.admin_users` used for authenticating operators and demo accounts into the Laravel Admin panel.
_Avoid_: User, member, profile

**IntegrationLog**:
An execution log tracking admin-initiated domain events, external webhook dispatches, payloads, response payloads, and delivery status.
_Avoid_: Audit log, activity log, error trace
