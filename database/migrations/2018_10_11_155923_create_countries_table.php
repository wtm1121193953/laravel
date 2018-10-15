<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('abbreviation')->default('')->comment('国家简称');
            $table->string('name_en')->default('')->comment('国家英文名');
            $table->string('name_zh')->default('')->comment('国家中文名');
            $table->string('code')->default('')->comment('国际区号');
            $table->string('flag_icon')->default('')->comment('国旗图片');
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
        Schema::dropIfExists('countries');
    }
}
