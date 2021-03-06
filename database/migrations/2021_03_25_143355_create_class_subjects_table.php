<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('create_by')->references('id')->on("lecturers")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('subject_id')->references('id')->on("subjects")->onDelete('restrict')->onUpdate('cascade');
            $table->string("name");
            $table->string("description")->nullable();
            $table->text("img");
            $table->tinyInteger("isActive")->default(1);
            $table->string("key", 10);
            $table->tinyInteger("semester")->comment("Học kỳ");
            $table->integer("maximum_group_member")->default(3)->comment("Thành viên tối đa trong nhóm");
            $table->tinyInteger("student_create_group")->default(0)->comment("Sinh viên có thể tự tạo nhóm");
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
        Schema::dropIfExists('class_subjects');
    }
}
