<?php

use App\Modules\Merchant\MerchantService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Modules\Merchant\Merchant;

class AddLowestAmountToMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->decimal('lowest_amount')->default(0)->comment('最低消费');
        });
        Merchant::chunk(1000, function($merchants){
            foreach ($merchants as $merchant){
                $merchant->lowest_amount = MerchantService::getLowestPriceForMerchant($merchant->id);
                $merchant->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('merchants', function (Blueprint $table){
            $table->dropColumn([
                'lowest_amount',
            ]);
        });
    }
}
