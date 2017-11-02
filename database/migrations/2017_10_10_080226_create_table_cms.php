<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCms extends Migration
{

    public function up()
    {
        if(!Schema::hasTable('cms'))
        {
            Schema::create('cms', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('name');
                $table->string('title')->nullable();
                $table->longText('content');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cms');
    }
}
