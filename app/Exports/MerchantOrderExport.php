<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 15:58
 */

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MerchantOrderExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->query;
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
        if($row->type ==3 && count($row->dishes_items) ==1){
            $goosName = $row->dishes_items[0]->dishes_goods_name;
        }elseif($row->type ==3 && count($row->dishes_items) >1){
            $goosName = $row->dishes_items[0]->dishes_goods_name.'等'.count($row->dishes_items).'件商品';
        }elseif($row->type ==2){
            $goosName = '无';
        }else{
            $goosName = $row-> goods_name;
        }
        if($row->pay_type ==1){
            $payType = '微信';
        }elseif($row->pay_type == 2){
            $payType = '支付宝';
        }elseif($row->pay_type == 3){
            $payType = '融宝';
        }else{
            $payType = '未知('.$row->pay_type.')';
        }
        return [
            $row->id,
            $row->created_at,
            $row->order_no,
            ['','团购','买单','单品'][$row->type],
            $goosName,
            $row->pay_price,
            $row->notify_mobile,
            ['','未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成'][$row->status],
            $payType
        ];
    }
}