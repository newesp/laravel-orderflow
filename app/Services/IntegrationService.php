<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class IntegrationService
{
    public function log(
        string $eventType,
        string $refType,
        string $refId,
        string $target = 'system',
        string $status = 'success',
        ?array $payload = null,
        ?array $response = null,
        ?string $errorMessage = null
    ): IntegrationLog {
        return IntegrationLog::create([
            'event_type' => $eventType,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'target' => $target,
            'status' => $status,
            'payload' => $payload,
            'response' => $response,
            'error_message' => $errorMessage,
        ]);
    }

    public function handleOrderStatusChanged(string $orderId, string $fromStatus, string $toStatus, int $total): void
    {
        $payload = [
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'total' => $total,
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Record primary internal system log
        $this->log(
            eventType: 'order.status_changed',
            refType: 'order',
            refId: $orderId,
            target: 'internal-audit',
            status: 'success',
            payload: $payload
        );

        // 2. Dispatch optional external webhook if configured
        $webhookUrl = config('services.webhook.url') ?? env('DEMO_WEBHOOK_URL');

        if (!empty($webhookUrl)) {
            try {
                $response = Http::timeout(3)
                    ->acceptJson()
                    ->post($webhookUrl, [
                        'event' => 'order.status_changed',
                        'data' => $payload,
                    ]);

                if ($response->successful()) {
                    $this->log(
                        eventType: 'webhook.dispatched',
                        refType: 'order',
                        refId: $orderId,
                        target: $webhookUrl,
                        status: 'success',
                        payload: $payload,
                        response: $response->json() ?? ['status_code' => $response->status()]
                    );
                } else {
                    $this->log(
                        eventType: 'webhook.failed',
                        refType: 'order',
                        refId: $orderId,
                        target: $webhookUrl,
                        status: 'failed',
                        payload: $payload,
                        response: $response->json() ?? ['status_code' => $response->status()],
                        errorMessage: "HTTP error status: " . $response->status()
                    );
                }
            } catch (Throwable $e) {
                Log::warning("Failed to dispatch webhook to {$webhookUrl}: " . $e->getMessage());

                $this->log(
                    eventType: 'webhook.failed',
                    refType: 'order',
                    refId: $orderId,
                    target: $webhookUrl,
                    status: 'failed',
                    payload: $payload,
                    errorMessage: $e->getMessage()
                );
            }
        }
    }
}
