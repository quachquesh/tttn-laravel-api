<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->references('id')->on("class_members")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('group_id')->references('id')->on("class_groups")->onDelete('restrict')->onUpdate('cascade');
            $table->tinyInteger("role")->default(0)->comment("0: thành viên, 1: nhóm trưởng");
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
        Schema::dropIfExists('group_members');
    }
}
