<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_notice', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(1)->comment('用户ID');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->comment('内容');
            $table->tinyinteger('is_read')->dafault(1)->comment('是否阅读 1：未读；2：已阅-用于记录是否查看详情');
            $table->tinyinteger('is_view')->default(1)->comment('是否阅览 1：未阅；2：已阅-用于统计小红圈数');
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
        Schema::dropIfExists('message_notice');
    }
}
