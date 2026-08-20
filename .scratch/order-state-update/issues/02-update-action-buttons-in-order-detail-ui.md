# 02 ??Update Action Buttons in Order Detail UI

**What to build:** The Order Detail page (`show.blade.php`) shows the correct transition buttons based on the order's current state. Specifically, updating buttons for `pending`, `processing`, and introducing buttons for `received`.

**Blocked by:** 01 ??Add "Received" Status to Order List and Filters

**Status:** ready-for-agent

- [x] Update `resources/views/admin/orders/show.blade.php` to conditionally render action buttons based on `$allowedNext`.
- [x] In `pending`, show "Mark as Processing" and "Cancel Order".
- [x] In `processing`, show "Waiting for customer receipt confirmation" and "Cancel Order". Hide "Mark as Completed" and "Mark as Received".
- [x] In `received`, show "Customer confirmed receipt" and "Mark as Completed". Hide "Cancel Order".
