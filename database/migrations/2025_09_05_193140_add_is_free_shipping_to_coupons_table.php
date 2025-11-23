<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFreeShippingToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_free_shipping')->nullable();
            $table->text('excluded_product_categories')->nullable();
            $table->text('excluded_product_ids')->nullable();
            $table->string('discount_type')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('limit_usage_to_x_items')->nullable();


            $table->text('product_ids')->nullable();
            $table->text('product_categories')->nullable();
            $table->float('minimum_amount')->nullable();
            $table->float('maximum_amount')->nullable();
            $table->text('product_brands')->nullable();
            $table->text('exclude_product_brands')->nullable();
            $table->boolean('exclude_sale_items')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('is_free_shipping');
            $table->dropColumn('excluded_product_categories');
            $table->dropColumn('excluded_product_ids');
            $table->dropColumn('product_ids');
            $table->dropColumn('product_categories');
            $table->dropColumn('minimum_amount');
            $table->dropColumn('maximum_amount');
            $table->dropColumn('product_brands');
            $table->dropColumn('exclude_product_brands');
            $table->dropColumn('discount_type');
            $table->dropColumn('usage_limit_per_user');
            $table->dropColumn('limit_usage_to_x_items');
        });
    }
};
