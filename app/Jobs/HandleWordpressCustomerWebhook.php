<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Wordpress\Webhook\WordpressCustomerController;

class HandleWordpressCustomerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $action
    ) {}

    public function handle(WordpressCustomerController $controller): void
    {
        match ($this->action) {
            'create' => $controller->handleCustomerCreated($this->payload),
            'update' => $controller->handleCustomerUpdated($this->payload),
            'delete' => $controller->handleCustomerDeleted($this->payload),
        };
    }
}
