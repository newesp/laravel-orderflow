<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Services\IntegrationService;

class LogProductUpdated
{
    public function __construct(
        protected IntegrationService $integrationService
    ) {}

    public function handle(ProductUpdated $event): void
    {
        $this->integrationService->handleProductUpdated(
            productId: $event->product->id,
            changes: $event->changes
        );
    }
}
