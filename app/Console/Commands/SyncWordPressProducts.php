<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncWordPressProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:wordpress-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync wordpress products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://maximfood.com/wp-json/wc/v3/products', [
            'consumer_key' => env('CONSUMER_KEY'),
            'consumer_secret' => env('CONSUMER_SECRET'),
            'orderby' => 'date',
            'order' => 'desc'
        ]);

        if ($response->successful()) {
            $products = $response->json();

            foreach ($products as $product) {
                $productRecord = Product::updateOrCreate(
                    ['wordpress_id' => $product['id']],
                    [
                        'name' => $product['name'],
                        'slug' => $product['slug'],
                        'details' => $product['description'] ?? $product['short_description'],
                        'unit_price' => (float) $product['price'],
                        'purchase_price' => (float) ($product['regular_price'] ?? $product['price']),
                        'current_stock' => (int) ($product['stock_quantity'] ?? 0),
                        'minimum_order_qty' => (int) ($product['min_qty'] ?? 1),
                        'published' => $product['status'] === 'publish' ? 1 : 0,
                        'status' => $product['status'] === 'publish' ? 1 : 0,
                        'featured_status' => in_array('featured', $product['tags'] ?? []) ? 1 : 0,
                        'images' => json_encode($product['images'] ?? []),
                        'thumbnail' => $product['images'][0]['src'] ?? null,
                        'meta_title' => $product['name'],
                        'meta_description' => strip_tags($product['short_description'] ?? ''),
                        'tax' => (string) ($product['tax_class'] ?? '0.00'),
                        'discount' => $this->calculateDiscount($product),
                        'discount_type' => $this->getDiscountType($product),
                        'free_shipping' => $product['shipping_required'] ? 0 : 1,
                        'refundable' => 1, // افتراضي
                        'product_type' => 'physical', // افتراضي
                    ]
                );

                // مزامنة جدول product_stocks
                $this->syncProductStocks($productRecord, $product);
            }
        }
    }


    private function syncProductStocks($productRecord, $wpProduct)
    {
        // حذف المخزون القديم
        ProductStock::where('product_id', $productRecord->id)->delete();

        // إضافة المخزون الجديد
        if (!empty($wpProduct['variations'])) {
            // منتج له variations
            foreach ($wpProduct['variations'] as $variationId) {
                $variation = $this->getProductVariation($variationId);
                if ($variation) {
                    ProductStock::create([
                        'product_id' => $productRecord->id,
                        'variant' => $this->formatVariant($variation['attributes']),
                        'sku' => $variation['sku'] ?? '',
                        'price' => (float) $variation['price'],
                        'qty' => (int) ($variation['stock_quantity'] ?? 0),
                    ]);
                }
            }
        } else {
            // منتج بسيط بدون variations
            ProductStock::create([
                'product_id' => $productRecord->id,
                'variant' => null,
                'sku' => $wpProduct['sku'] ?? '',
                'price' => (float) $wpProduct['price'],
                'qty' => (int) ($wpProduct['stock_quantity'] ?? 0),
            ]);
        }
    }

    private function getProductVariation($variationId)
    {
        $response = Http::get("https://maximfood.com/wp-json/wc/v3/products/variations/{$variationId}", [
            'consumer_key' => env('CUSTOMER_KEY'),
            'consumer_secret' => env('CUSTOMER_SECRET'),
        ]);

        return $response->successful() ? $response->json() : null;
    }

    private function formatVariant($attributes)
    {
        if (empty($attributes)) {
            return null;
        }

        $variants = [];
        foreach ($attributes as $attribute) {
            $variants[] = $attribute['name'] . ': ' . $attribute['option'];
        }

        return implode(', ', $variants);
    }


}
