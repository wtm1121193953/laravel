<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Modules\Merchant\Merchant;
use App\Modules\Goods\Goods;

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
        $merchants = Merchant::all();
        foreach ($merchants as $merchant){
            $merchant->lowest_amount = Goods::getLowestPriceForMerchant($merchant->id) ?: 0;
            $merchant->save();
        }
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
