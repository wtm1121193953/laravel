<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageSystemUserBehaviorRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_system_user_behavior_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0)->index()->comment('用户ID');
            $table->string('is_read',255)->default('')->comment('是否阅读 保存阅读ID数组json');
            $table->string('is_view',255)->default('')->comment('是否阅览 保存阅览ID数组json');
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
        Schema::dropIfExists('message_system_user_behavior_record');
    }
}
