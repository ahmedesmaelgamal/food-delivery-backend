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
        Schema::create('shipping_zone_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_id');
            $table->text('method_description');
            $table->decimal('min_amount', 8, 2);
            $table->boolean('ignore_discounts_value')->default(false);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('shipping_zone_id');
            $table->unsignedInteger('wordpress_id')->unique();
            $table->boolean('is_notified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_methods');
    }
};
