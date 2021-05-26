<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->references('id')->on("class_members")->onDelete('cascade');
            $table->foreignId('member_target')->nullable()->references('id')->on("class_members")->onDelete('cascade');
            $table->tinyInteger("ticket_type")->default(0)->comment("0: xin vào nhóm, 1: xin chuyển nhóm, 2: kick khỏi nhóm");
            $table->text("reason")->comment("Lý do tạo ticket");
            $table->foreignId('group_now')->nullable()->references('id')->on("class_groups")->onDelete('cascade')->comment("Nhóm đang ở hiện tại, null là chưa tham gia nhóm");
            $table->foreignId('group_going')->nullable()->references('id')->on("class_groups")->onDelete('cascade')->comment("Nhóm xin tham gia, xin chuyển");
            $table->tinyInteger("status")->default(0)->comment("0: chưa duyệt, 1: đồng ý, 2: từ chối");
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
        Schema::dropIfExists('ticket_groups');
    }
}
