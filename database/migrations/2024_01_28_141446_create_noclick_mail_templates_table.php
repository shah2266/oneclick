<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoclickMailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('noclick_mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->unique();
            $table->string('to_email_addresses');
            $table->string('cc_email_addresses'); // Change to the appropriate data type
            $table->string('subject');
            $table->string('has_subject_date')->default(1);
            $table->string('greeting');
            $table->longText('mail_body_content')->nullable();
            $table->boolean('has_inline_date')->default(1);
            $table->string('has_custom_mail_template');
            $table->boolean('signature')->default(1);
            $table->tinyInteger('status')->default(1)->comment("1 is Active");
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
        Schema::dropIfExists('noclick_mail_templates');
    }
}
