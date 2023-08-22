<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FileStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_storage', function (Blueprint $table) {
            $table->id();
            //////////////////
            $table->text('name')->nullable();
            $table->text('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('original_extension')->nullable();
            $table->integer('size')->nullable();
            $table->mediumText('path')->nullable();
            $table->mediumText('public_path')->nullable();
            $table->string('attach_table')->nullable();
            $table->string('attach_id')->nullable();
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
        Schema::dropIfExists('file_storage');
    }
}
