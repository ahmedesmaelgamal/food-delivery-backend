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
        Schema::create('buy_it_togethers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wordpress_id');
            $table->boolean('is_notified')->default(false);
            $table->string('title');
            $table->string('woodmart_main_products_discount');
            $table->string('woodmart_fbt_product_id');
            $table->string('woodmart_fbt_product_discount');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_it_togethers');
    }
};
