<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminAuthGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_auth_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('分组名');
            $table->tinyInteger('status')->index()->default(1)->comment('状态: 1-有效  2-无效');
            $table->string('rule_ids', 500)->default('')->comment('权限ID列表, 逗号分隔');
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
        Schema::dropIfExists('admin_auth_groups');
    }
}
