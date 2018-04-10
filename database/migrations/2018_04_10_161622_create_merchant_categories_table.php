<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pid')->index()->default(0)->comment('父ID');
            $table->string('name')->default('')->comment('类别名称');
            $table->string('icon')->default('')->comment('类别图标url');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->timestamps();
            $table->softDeletes();

            $table->comment = '商家类别表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_categories');
    }
}
