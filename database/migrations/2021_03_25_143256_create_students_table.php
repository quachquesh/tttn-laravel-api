<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string("email")->nullable();
            $table->string("mssv");
            $table->string("password");
            $table->tinyInteger("isActive")->default(1);
            $table->string("first_name", 50);
            $table->string("last_name", 20);
            $table->tinyInteger("sex")->comment("0: Nam, 1: Nữ");
            $table->date("birthday");
            $table->string("phone_number", 11)->nullable();
            $table->string("address");
            $table->foreignId('create_by')->references('id')->on("lecturers")->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('students');
    }
}
