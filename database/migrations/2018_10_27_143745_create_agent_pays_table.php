<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_pays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('代付方式名称');
            $table->string('logo_url')->default('')->comment('logo地址');
            $table->string('class_name')->default('')->comment('对应类名');
            $table->tinyInteger('status')->default(0)->comment('状态1启用 2禁用');
            $table->text('configs')->comment('配置信息');
            $table->timestamps();
            $table->comment = '代付方式';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_pays');
    }
}
