<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnlineFieldToServicesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_online')->default(0);
            $table->string('location_address')->nullable();
            $table->string('location_phone_number')->nullable();
            $table->string('location_description')->nullable();
            $table->string('location_latitude')->nullable();
            $table->string('location_longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'is_online')) {
                $table->dropColumn('is_online');
            }
            if (Schema::hasColumn('services', 'location_address')) {
                $table->dropColumn('location_address');
            }
            if (Schema::hasColumn('services', 'location_phone_number')) {
                $table->dropColumn('location_phone_number');
            }
            if (Schema::hasColumn('services', 'location_description')) {
                $table->dropColumn('location_description');
            }
            if (Schema::hasColumn('services', 'location_latitude')) {
                $table->dropColumn('location_latitude');
            }
            if (Schema::hasColumn('services', 'location_longitude')) {
                $table->dropColumn('location_longitude');
            }
        });
    }
}
