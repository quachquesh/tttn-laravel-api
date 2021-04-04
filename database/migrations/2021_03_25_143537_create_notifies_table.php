<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_id')->nullable()->references('id')->on("lecturers")->onDelete('restrict');
            $table->foreignId('member_id')->nullable()->references('id')->on("class_members")->onDelete('cascade');
            $table->foreignId('class_id')->references('id')->on("class_subjects")->onDelete('restrict');
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
        Schema::dropIfExists('notifies');
    }
}
