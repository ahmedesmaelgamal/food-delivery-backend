<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWordpressWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the secret from config/services.php
        $secret = config('services.woocommerce.webhook_secret');

        // Get WooCommerce webhook signature from headers
        $signature = $request->header('x-wc-webhook-signature');

        if (!$signature || !$secret) {
            abort(401, 'Missing webhook signature or secret.');
        }

        // Get raw request body (must be exactly as WooCommerce sent it)
        $payload = $request->getContent();

        // Calculate expected signature
        $calculated = base64_encode(
            hash_hmac('sha256', $payload, $secret, true)
        );

        // Compare securely
        if (!hash_equals($calculated, $signature)) {
            abort(401, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
