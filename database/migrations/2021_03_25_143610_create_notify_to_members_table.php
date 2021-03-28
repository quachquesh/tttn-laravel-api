<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifyToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notify_to_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->references('id')->on("class_members")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('notify_id')->references('id')->on("notifies")->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('notify_to_members');
    }
}
