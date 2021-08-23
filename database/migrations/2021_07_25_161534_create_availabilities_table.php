<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('start_hour');
            $table->integer('end_hour');
            $table->integer('start_minute');
            $table->integer('end_minute');
            $table->integer('day_of_week');

            $table->timestamps();
        });

        Schema::create('availability_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('availability_service');
        Schema::dropIfExists('availabilities');
    }
}
