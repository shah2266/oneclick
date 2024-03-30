<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoclickCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('noclick_commands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('command')->unique();
            $table->unsignedBigInteger('mail_template_id');
            $table->enum('status', ['on', 'off'])->default('on');
            $table->tinyInteger('user_id');
            $table->timestamps();
            $table->foreign('mail_template_id')->references('id')->on('noclick_mail_templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('noclick_commands');
    }
}
