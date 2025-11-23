<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Wordpress\Webhook\WordpressOrderController;


class HandleWordpressOrderWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $action
    ) {}

    public function handle(WordpressOrderController $controller): void
    {
        match ($this->action) {
            'create' => $controller->handleOrderCreated($this->payload),
            'update' => $controller->handleOrderUpdated($this->payload),
            'delete' => $controller->handleOrderDeleted($this->payload),
        };
    }
}
