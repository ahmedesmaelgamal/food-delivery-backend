<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Http\Controllers\Wordpress\Webhook\WordpressProductController;

class HandleWordpressProductWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $action
    ) {}

    public function handle(WordpressProductController $controller): void
    {
        match ($this->action) {
            'create' => $controller->handleProductCreated($this->payload),
            'update' => $controller->handleProductUpdated($this->payload),
            'delete' => $controller->handleProductDeleted($this->payload),
        };
    }
}
