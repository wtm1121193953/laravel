<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 18:40
 */

namespace App\Exports;

use App\Modules\Order\Order;
use App\Modules\Payment\Payment;
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
            'merchant_id' => '商户ID',
            'merchant_name' => '商户名',
            'order_no' => '订单号',
            'type' => '订单类型',
            'goods_id' => '商品ID',
            'goods_name' => '商品名',
            'price' => '价格',
            'buy_number' => '购买数量',
            'user_id' => '用户ID',
            'user_name' => '用户名',
            'notify_mobile' => '通知手机号',
            'status' => '状态',
            'pay_type' => '支付方式',
            'pay_price' => '支付金额',
            'pay_time' => '支付时间',
            'pay_target_type' => '支付对象',
            'refund_price' => '退款金额',
            'refund_time' => '退款时间',
            'finish_time' => '订单核销时间',
            'created_at' => '创建时间',
            'origin_app_type' => '下单客户端类型',
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
        return [
            $row->merchant_id,
            $row->merchant_name,
            $row->order_no,
            Order::getTypeText($row->type),
            $row->goods_id,
            $row->goods_name,
            $row->price,
            $row->buy_number,
            $row->user_id,
            $row->user_name,
            $row->notify_mobile,
            Order::getStatusText($row->status),
            $payments[$row->pay_type]??'未知('.$row->pay_type.')',
            $row->pay_price,
            $row->pay_time,
            Order::getPayTargetTypeText($row->pay_target_type),
            $row->refund_price,
            $row->refund_time,
            $row->finish_time,
            $row->created_at,
            Order::getOriginAppTypeText($row->origin_app_type),
            $row->remark,
        ];
    }

}