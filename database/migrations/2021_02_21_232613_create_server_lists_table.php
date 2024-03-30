<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServerListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_lists', function (Blueprint $table) {
            $table->id();
            $table->string('machineName',100)->nullable();
            $table->string('shortName', 50)->nullable();
            $table->string('ipAddress',25)->unique();
            $table->string('operatingSystem', 100);
            $table->string('manufacturer', 100)->nullable();
            $table->string('model', 100);
            $table->string('bios', 100)->nullable();
            $table->string('processor', 100)->nullable();
            $table->string('hdd', 100)->nullable();
            $table->string('memoryRam', 100)->nullable();
            $table->string('osMemory', 100)->nullable();
            $table->string('applicationRunning', 100)->nullable();
            $table->longText('comment')->nullable();
            $table->tinyInteger('status')->default(1)->comment("1 is Visible");
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
        Schema::dropIfExists('server_lists');
    }
}
