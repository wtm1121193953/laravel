<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDishesCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dishes_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('单品分类名称');
            $table->integer('sort')->default(1)->comment('排序');
            $table->tinyInteger('status')->index()->default(1)->comment('1-上架， 2-下架');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '单品分类列表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dishes_categories');
    }
}
