<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('运营中心名称');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-禁用');
            $table->timestamps();
            $table->softDeletes();

            $table->comm2ent = '运营中心表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opers');
    }
}
