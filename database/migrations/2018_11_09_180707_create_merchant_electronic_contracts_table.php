<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantElectronicContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_electronic_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->index()->default(0)->comment('商户ID');
            $table->string('el_contract_no')->default('')->comment('电子合同编号');
            $table->dateTime('sign_time')->nullable()->comment('签约时间');
            $table->dateTime('expiry_time')->nullable()->comment('过期时间');
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
        Schema::dropIfExists('merchant_electronic_contracts');
    }
}
