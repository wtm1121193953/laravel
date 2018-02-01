<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminAuthRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_auth_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('权限名');
            $table->integer('level')->default(1)->comment('层级, 通过所属父权限计算出来');
            $table->string('url', 500)->default('')->comment('菜单链接');
            $table->string('url_all', 500)->default('')->comment('权限全部菜单地址, 使用逗号分隔');
            $table->string('icon', 50)->default('')->comment('菜单图标');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->index()->default(1)->comment('状态: 1-有效  2-无效');
            $table->integer('pid')->index()->default(0)->comment('父权限ID');
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
        Schema::dropIfExists('admin_auth_rules');
    }
}
