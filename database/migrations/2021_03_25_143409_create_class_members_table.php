<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->references('id')->on("students")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('class_id')->references('id')->on("class_subjects")->onDelete('restrict')->onUpdate('cascade');
            $table->tinyInteger("role")->default(0)->comment("0: bình thường, 1: lớp trưởng");
            $table->tinyInteger("isActive")->default(1);
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
        Schema::dropIfExists('class_members');
    }
}
