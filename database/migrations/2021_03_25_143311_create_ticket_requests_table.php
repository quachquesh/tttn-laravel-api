<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_send')->references('id')->on("students")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('lecturer_take')->references('id')->on("lecturers")->onDelete('restrict')->onUpdate('cascade');
            $table->string("title");
            $table->string("content");
            $table->tinyInteger("status")->default(0)->comment("0: Chưa xem, 1: Đã xem");
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
        Schema::dropIfExists('ticket_requests');
    }
}
