<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketRequestRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_request_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->references('id')->on("ticket_requests")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('reply_by')->references('id')->on("lecturers")->onDelete('restrict')->onUpdate('cascade');
            $table->string("content");
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
        Schema::dropIfExists('ticket_request_replies');
    }
}
