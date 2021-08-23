<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLiveMeetingFieldsToAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            $table->text('meeting_start_url')->nullable();
            $table->text('meeting_join_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_id',
                'meeting_password',
                'meeting_start_url',
                'meeting_join_url',
            ]);
        });
    }
}
