<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('app_name')->index()->default('')->comment('应用名称');
            $table->string('app_tag')->default('')->comment('版本标签');
            $table->string('app_num')->default('')->comment('版本号');
            $table->integer('version_num')->default(0)->comment('版本序号');
            $table->string('version_explain')->default('')->comment('更新说明');
            $table->string('package_url')->default('')->comment('安装包路径');
            $table->integer('status')->default(1)->comment('发布状态,1-暂不发布;2-已发布');
            $table->integer('force_update')->default(1)->comment('强制更新,1-否,2-是');
            $table->integer('app_type')->default(1)->comment('应用类型,1-IOS,2-Android');
            $table->timestamps();
            $table->comment = '版本管理表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('versions');
    }
}
