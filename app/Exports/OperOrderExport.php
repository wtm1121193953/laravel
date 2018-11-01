<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 18:40
 */

namespace App\Exports;

use App\Modules\Order\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 运营中心订单导出
 * Class OperOrderExport
 * @package App\Exports
 */
class OperOrderExport implements FromCollection, WithMapping, WithHeadings
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
     * 定义表头
     * @return array
     */
    public function headings(): array
    {
        return [
            'id' => 'ID',
            'merchant_name' => '所属商户',
            'created_at' => '创建时间',
            'order_no' => '订单号',
            'type' => '订单类型',
            'goods_name' => '商品名称',
            'pay_price' => '总价 ¥',
            'pay_type' => '支付方式',
            'status' => '订单状态',
            'pay_time' => '支付时间',
            'finish_time' => '核销时间',
            'notify_mobile' => '手机号',
            'remark' => '备注',
        ];
    }

    /**
     * 定义数据结构
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $payments = [1=>'微信',2=>'支付宝',3=>'融宝'];
        if($row->finish_time && $row->type == 1){
            $finish_time = $row->finish_time;
        }else{
            $finish_time = '';
        }
        return [
            $row->id,
            $row->merchant_name,
            $row->created_at,
            $row->order_no,
            Order::getTypeText($row->type),
            Order::getGoodsNameText($row->type,$row->dishes_items,$row->goods_name),
            $row->pay_price,
            $payments[$row->pay_type]??'未知('.$row->pay_type.')',
            Order::getStatusText($row->status),
            $row->pay_time,
            $finish_time,
            $row->notify_mobile,
            $row->remark,
        ];
    }

}