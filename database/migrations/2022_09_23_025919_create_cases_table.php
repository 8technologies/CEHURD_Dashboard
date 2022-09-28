<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->bigInteger('case_category_id');
            $table->bigInteger('district');
            $table->bigInteger('sub_county');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('response')->nullable();
            $table->text('status')->nullable();
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cases');
    }
}
