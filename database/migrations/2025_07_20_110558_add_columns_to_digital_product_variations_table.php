<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('digital_product_variations', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('regular_price')->nullable();
            $table->string('sale_price')->nullable();
            $table->dateTime('date_on_sale_from')->nullable();
            $table->dateTime('date_on_sale_to')->nullable();
            $table->boolean('on_sale')->default(false);
            $table->string('tax_status');
            $table->string('stock_status');
            $table->string('image');
            $table->json('attributes')->nullable();
            $table->integer('menu_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_product_variations', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'regular_price',
                'sale_price',
                'date_on_sale_from',
                'date_on_sale_to',
                'on_sale',
                'tax_status',
                'stock_status',
                'image',
                'attributes',
                'menu_order'
            ]);
        });
    }
};
