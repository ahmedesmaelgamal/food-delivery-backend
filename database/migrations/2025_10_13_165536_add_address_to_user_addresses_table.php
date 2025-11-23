<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressToUserAddressesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->text('floor_number')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('address')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('floor_number');
            $table->dropColumn('phone_number');;
            $table->dropColumn('address');
        });
    }
};
