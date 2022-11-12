<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('facilitator_name')->nullable();
            $table->text('facilitator_title')->nullable();
            $table->integer('sub_county')->nullable();
            $table->integer('district')->nullable();
            $table->date('activity_date')->nullable();
            $table->text('activity_venue')->nullable();
            $table->text('activity_description')->nullable();
            $table->text('how_issues_will_be_followed_up')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('challanges_solutions')->nullable();
            $table->text('challanges_faced')->nullable();
            $table->text('issues_raised')->nullable();
            $table->integer('activity_duration')->nullable();
            $table->integer('number_of_conducted')->nullable();
            $table->integer('number_of_attended')->nullable();
            $table->integer('reported_by')->nullable();
            $table->integer('approved_by')->nullable();
            $table->text('status')->nullable();
        });
    }






    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_reports');
    }
}
