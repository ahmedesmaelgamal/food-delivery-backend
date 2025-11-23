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
        Schema::create('shipping_zone_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('type');
            $table->unsignedBigInteger('wordpress_id');
            $table->unsignedBigInteger('shipping_zone_id')->nullable();
            $table->boolean('is_notified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_locations');
    }
};
