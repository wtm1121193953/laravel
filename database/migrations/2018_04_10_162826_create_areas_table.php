<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('area_id')->index()->comment('地区id');
            $table->string('name', 30)->comment('地区名称');
            $table->tinyInteger('type')->default(0)->comment('类型，保留字段');
            $table->tinyInteger('path')->default(1)->comment('路径，从1开始');
            $table->string('area_code', 4)->index()->comment('区号');
            $table->string('spell', 50)->index()->comment('拼音');
            $table->string('letter', 15)->index()->comment('简拼');
            $table->string('first_letter', 1)->index()->comment('首字母');
            $table->tinyInteger('status')->default(1)->index()->comment('状态 1-正常 2-禁用');
            $table->integer('parent_id')->default(0)->index()->comment('父ID，如果是省份，则父ID为0');

            $table->comment = '地区表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
