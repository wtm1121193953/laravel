<?php

namespace App\Imports;

use App\Exceptions\BaseResponseException;
use App\Modules\Order\OrderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class BatchDelivery implements ToCollection
{
    protected $merchantId;

    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        try {
            DB::beginTransaction();
            $merchantId = $this->merchantId;
            $arr = $collection->toArray();
            array_shift($arr);
            $arr = array_filter($arr, function ($v) {
                if (!$v[0] && !$v[1] && !$v[2]) {
                    return false;
                } else {
                    return true;
                }
            });
            if (count($arr) > 200) {
                throw new \Exception('批量发货不能超过200条');
            }
            foreach ($arr as $item) {
                OrderService::deliverByOrderNo($item[0], $merchantId, $item[1], $item[2]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('批量发货错误info', [
                'msg' => $e->getMessage(),
                'data' => $e,
            ]);
            throw new BaseResponseException($e->getMessage());
        }
    }
}
