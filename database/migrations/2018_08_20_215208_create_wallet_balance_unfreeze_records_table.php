<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletBalanceUnfreezeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_balance_unfreeze_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id')->index()->default(0)->comment('钱包ID');
            $table->integer('fee_splitting_record_id')->index()->default(0)->comment('分润记录ID');
            $table->decimal('unfreeze_amount', 11, 2)->default(0)->comment('解冻金额');
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
        Schema::dropIfExists('wallet_balance_unfreeze_records');
    }
}
