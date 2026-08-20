<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Order;

class OrderStatusService
{
    /**
     * Define allowed directional status transitions.
     */
    protected array $allowedTransitions = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return true;
        }

        $allowed = $this->allowedTransitions[$fromStatus] ?? [];

        return in_array($toStatus, $allowed, true);
    }

    public function getAllowedNextStatuses(string $currentStatus): array
    {
        return $this->allowedTransitions[$currentStatus] ?? [];
    }

    /**
     * Transition order to a new status.
     *
     * @throws InvalidOrderStatusTransitionException
     */
    public function transition(Order $order, string $newStatus): Order
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $newStatus) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            
            $currentStatus = $lockedOrder->status;

            if ($currentStatus === $newStatus) {
                return $lockedOrder;
            }

            if (!$this->canTransition($currentStatus, $newStatus)) {
                throw new InvalidOrderStatusTransitionException($currentStatus, $newStatus);
            }

            $lockedOrder->update([
                'status' => $newStatus,
            ]);

            // Dispatch domain event for logging and webhooks
            event(new OrderStatusChanged($lockedOrder, $currentStatus, $newStatus));

            return $lockedOrder;
        });
    }
}
