<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppServiceCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appService_categories', function (Blueprint $table) {
            $table->id();
            //////////////////
            $table->bigInteger('index')->nullable();
            $table->text('name')->nullable();
            $table->text('title')->nullable();
            $table->text('slug')->nullable();
            $table->string('platform')->nullable();
            $table->string('category')->nullable();
            $table->text('icon')->nullable();
            $table->text('img')->nullable();
            $table->text('banner')->nullable();
            $table->mediumText('tags')->nullable();
            $table->integer('points')->nullable();
            $table->string('discount_status')->nullable();
            $table->longText('summery')->nullable();
            $table->mediumText('regions')->nullable();
            $table->longText('notice')->nullable();
            $table->longText('warnings')->nullable();
            $table->longText('purchase_descriptions')->nullable();
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('appService_categories');
    }
}
