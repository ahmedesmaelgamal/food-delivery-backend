<?php

namespace App\Http\Controllers\Wordpress\webhook;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WordpressProductController extends Controller
{
    public function handleProductCreated(array $payload): void
    {
        DB::beginTransaction();

        try {
            $productRecord = Product::updateOrCreate(
                ['wordpress_id' => $payload['id']],
                $this->mapProductData($payload)
            );

            // Sync product stock
            $this->syncProductStock($productRecord->id, $payload);

            DB::commit();

            Log::info("Product created/updated via webhook", [
                'wordpress_id' => $payload['id'],
                'product_id' => $productRecord->id,
                'name' => $payload['name'] ?? 'Unknown'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product creation failed via webhook", [
                'wordpress_id' => $payload['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function handleProductUpdated(array $payload): void
    {
        // For updates, we use the same logic as creation (updateOrCreate)
        $this->handleProductCreated($payload);
    }

    public function handleProductDeleted(array $payload): void
    {
        DB::beginTransaction();

        try {
            $product = Product::where('wordpress_id', $payload['id'])->first();

            if ($product) {
                // Delete associated stock first
                ProductStock::where('product_id', $product->id)->delete();

                // Then delete the product
                $product->delete();

                Log::info("Product deleted via webhook", [
                    'wordpress_id' => $payload['id'],
                    'product_id' => $product->id,
                    'name' => $product->name
                ]);
            } else {
                Log::warning("Product not found for deletion", [
                    'wordpress_id' => $payload['id']
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product deletion failed via webhook", [
                'wordpress_id' => $payload['id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function mapProductData(array $product): array
    {
        return [
            'name' => $product['name'] ?? '',
            'slug' => $product['slug'] ?? '',
            'category_ids' => json_encode(array_column($product['categories'] ?? [], 'id')),
            'details' => $product['description'] ?? '',
            'description' => $product['short_description'] ?? '',
            'unit_price' => (float) ($product['price'] ?? 0),
            'sale_price' => (float) ($product['sale_price'] ?? 0),
            'on_sale' => $product['on_sale'] == true ? 1 : 0,
            'regular_price' => $product['regular_price'] ?: $product['price'],
            'purchase_price' => (float) ($product['regular_price'] ?? 0),
            'current_stock' => (int) ($product['stock_status'] == 'instock' ? 1 : 0),
            'published' => $product['status'] === 'publish' ? 1 : 0,
            'status' => $product['status'] === 'publish' ? 1 : 0,
            'images' => json_encode($product['images'] ?? []),
            'thumbnail' => $product['images'][0]['src'] ?? null,
            'meta_title' => $product['name'] ?? '',
            'meta_description' => strip_tags($product['short_description'] ?? ''),
            'refundable' => 1,
            'is_taxable' => $product['tax_status'] == 'taxable' ? 1 : 0,
            'product_type' => 'physical',
            'date_on_sale_from' => isset($product['date_on_sale_from']) ? strtotime($product['date_on_sale_from']) : null,
            'date_on_sale_to' => isset($product['date_on_sale_to']) ? strtotime($product['date_on_sale_to']) : null,
            'woodmart_fbt_bundles_id' => json_encode(collect($product['meta_data'])->firstWhere('key', 'woodmart_fbt_bundles_id')['value'] ?? []),
            'related_ids' => json_encode($product['related_ids'] ?? []),
            'lang' => 'en',
            'total_sales' => (int) ($product['total_sales'] ?? 0),
            'translation_ar' => $this->formatTranslation($product['translations']['ar'] ?? null),
            'translation_en' => $this->formatTranslation($product['translations']['en'] ?? null),
            'is_notified' => false
        ];
    }

    private function formatTranslation($translation)
    {
        if (!$translation) {
            return null;
        }

        return is_array($translation) ? json_encode($translation) : $translation;
    }

    private function syncProductStock($productId, array $product): void
    {
        ProductStock::updateOrCreate(
            ['product_id' => $productId],
            [
                'sku' => $product['sku'] ?? '',
                'price' => (float) ($product['price'] ?? 0),
                'qty' => (int) ($product['stock_quantity'] ?? 0),
            ]
        );
    }
}