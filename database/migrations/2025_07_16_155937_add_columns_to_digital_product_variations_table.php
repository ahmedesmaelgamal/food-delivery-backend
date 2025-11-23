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
            $table->float('sale_price',10,2)->nullable();
            $table->float( 'regular_price', 10, 2)->nullable();
            $table->float('date_on_sale_from', 10,2)->nullable();
            $table->float('date_on_sale_to', 10,2)->nullable();
            $table->string('stock_status')->nullable()->comment('Stock status of the product variation either "in stock", "out of stock", or "null"');
            $table->string('option')->nullable()->comment('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_product_variations', function (Blueprint $table) {
        $table->dropColumn('sale_price');
        $table->dropColumn('regular_price');
        });
    }
};
