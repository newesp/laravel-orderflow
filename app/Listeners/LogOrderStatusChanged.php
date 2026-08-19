<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\IntegrationService;

class LogOrderStatusChanged
{
    public function __construct(
        protected IntegrationService $integrationService
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $this->integrationService->handleOrderStatusChanged(
            orderId: $event->order->id,
            fromStatus: $event->fromStatus,
            toStatus: $event->toStatus,
            total: $event->order->total
        );
    }
}
