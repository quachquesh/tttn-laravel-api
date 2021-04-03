<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->references('id')->on("class_members")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('class_id')->references('id')->on("class_subjects")->onDelete('restrict')->onUpdate('cascade');
            $table->string("title");
            $table->text("content");
            $table->tinyInteger("isReply")->default(1)->comment("0: khóa bình luận, 1: mở bình luận");
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
        Schema::dropIfExists('questions');
    }
}
