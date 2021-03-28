<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->references('id')->on("class_members")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('question_id')->references('id')->on("questions")->onDelete('restrict')->onUpdate('cascade');
            $table->text("content");
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
        Schema::dropIfExists('question_replies');
    }
}
