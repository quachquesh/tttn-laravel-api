<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifyRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notify_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notify_id')->references('id')->on("notifies")->onDelete('cascade');
            $table->foreignId('reply_by_member')->nullable()->references('id')->on("class_members")->onDelete('cascade');
            $table->foreignId('reply_by_lecturer')->nullable()->references('id')->on("lecturers")->onDelete('cascade');
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
        Schema::dropIfExists('notify_replies');
    }
}
