<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoclickSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('noclick_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('frequency')->comment('This is frequency options');
            $table->unsignedBigInteger('command_id');
            $table->string('days')->nullable();
            $table->time('time');
            $table->string('holiday')->nullable();
            $table->enum('status', ['on', 'off'])->default('on');
            $table->tinyInteger('user_id');
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
        Schema::dropIfExists('noclick_schedules');
    }
}
