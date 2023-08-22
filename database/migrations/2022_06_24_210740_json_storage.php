<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class JsonStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('json_storage', function (Blueprint $table) {
            $table->id();
            //////////////////
            $table->string('name')->unique();
            $table->string('name_fa')->nullable();
            $table->string('type')->nullable();
            $table->text('form_file')->nullable();
            $table->json('data')->nullable();
            /////////////////////
            $table->unsignedBigInteger('timestamp')->nullable();
            $table->string('date')->nullable();
            $table->string('time')->nullable();
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
        Schema::dropIfExists('json_storage');
    }
}
