<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 15:58
 */

namespace App\Exports;

use App\Modules\Order\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MerchantOrderExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            '下单时间',
            '订单号',
            '订单类型',
            '商品名称',
            '总价￥',
            '手机号',
            '订单状态',
            '支付方式',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->created_at,
            $row->order_no,
            ['','团购','买单','单品'][$row->type],
            Order::getGoodsNameText($row->type,$row->dishes_items,$row->goods_name),
            $row->pay_price,
            $row->notify_mobile,
            Order::getStatusText($row->status),
            Order::getPayTypeText($row->pay_type),
        ];
    }
}