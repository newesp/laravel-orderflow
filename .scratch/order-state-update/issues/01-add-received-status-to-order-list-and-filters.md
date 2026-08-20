# 01 ??Add "Received" Status to Order List and Filters

**What to build:** Admins can see the `Received` badge in the Orders List, filter the list by `Received`, and the system's test schema and core validations (Controllers & API) recognize `received` as a valid status.

**Blocked by:** None ??can start immediately.

**Status:** ready-for-agent

- [x] Update `app/Http/Controllers/Admin/OrderController.php` validation rules and filters.
- [x] Update `app/Http/Controllers/Api/AdminOrderApiController.php` validation and filtering.
- [x] Update `resources/views/admin/orders/index.blade.php` to include `Received` in filter list and status badges.
- [x] Update test schema in `database/migrations/0001_01_01_000000_create_test_schema.php` to include `received`.
