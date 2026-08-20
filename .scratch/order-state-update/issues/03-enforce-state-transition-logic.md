# 03 ??Enforce State Transition Logic

**What to build:** The backend transition logic (`OrderStatusService`) enforces the new rules (`received -> completed` only, cancellation restrictions, etc.), and all valid and invalid transitions are fully covered by Feature tests (`AdminOrderTest.php`).

**Blocked by:** 02 ??Update Action Buttons in Order Detail UI

**Status:** ready-for-agent

- [x] Update `app/Services/OrderStatusService.php` to define exact `allowedTransitions`.
- [x] Update `tests/Feature/AdminOrderTest.php` Happy Path test to simulate `processing -> received` DB change and test `received -> completed`.
- [x] Update `tests/Feature/AdminOrderTest.php` to cover all valid transitions (pending->processing, pending->cancelled, processing->cancelled, received->completed).
- [x] Update `tests/Feature/AdminOrderTest.php` to assert failures for invalid transitions (e.g. received->cancelled, processing->received, processing->completed).
- [x] Ensure valid events `OrderStatusChanged` are emitted.
