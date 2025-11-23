<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Wordpress\Webhook\WordpressCouponController;

class HandleWordpressCouponWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $action
    ) {}

    public function handle(WordpressCouponController $controller): void
    {
//        \Log::info('in handle webhook: ' . json_encode($this->payload));
//        dd($this->payload);
        match ($this->action) {
            'create' => $controller->handleCouponCreated($this->payload),
            'update' => $controller->handleCouponUpdated($this->payload),
            'delete' => $controller->handleCouponDeleted($this->payload),
        };
    }
}
