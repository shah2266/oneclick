<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIofcompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iofcompanies', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->integer('precedence');
			$table->string('systemId');
			$table->string('shortName', 100);
			$table->string('fullName',100);
            $table->string('status')->default(1)->comment("1 is Visible");
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
        Schema::dropIfExists('iofcompanies');
    }
}
