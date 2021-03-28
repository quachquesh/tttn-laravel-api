<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->references('id')->on("class_subjects")->onDelete('restrict')->onUpdate('cascade');
            $table->string("name");
            $table->integer("member_min")->default(1);
            $table->integer("member_max")->default(5);
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
        Schema::dropIfExists('class_groups');
    }
}
