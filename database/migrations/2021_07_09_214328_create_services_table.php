<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('visible');
            $table->decimal('price', 15, 2)->nullable();
            $table->float('duration')->nullable();
            $table->integer('max_capacity')->nullable();
            $table->integer('min_capacity')->nullable();
            $table->foreignId('service_category_id');
            $table->string('image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('service_teacher_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id');
            $table->foreignId('teacher_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_teacher_profile');
        Schema::dropIfExists('services');
    }
}
