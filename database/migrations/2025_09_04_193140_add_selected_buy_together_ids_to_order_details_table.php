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
        Schema::table('order_details', function (Blueprint $table) {
            $table->text('selected_buy_together_ids')->nullable()->after('buy_together_price')->comment('selected buy together ids in order');
        });
    }


    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('selected_buy_together_ids');
        });
    }
};
