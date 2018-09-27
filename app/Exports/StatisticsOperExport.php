<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/8
 * Time: 15:25
 */

namespace App\Exports;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 运营中心营销报表导出
 * Class OperInviteRecordsExport
 * @package App\Jobs
 */
class StatisticsOperExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;
    protected $params;

    public function __construct($query,$params=[])
    {
        $this->query = $query;
        $this->params = $params;
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
            '时间',
            '运营中心id',
            '运营中心名称',
            '商户数',
            '邀请用户数',
            '总订单量（已支付）',
            '总退款量',
            '总订单金额（已支付）',
            '总退款金额',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $row->date = "{$this->params['startDate']}至{$this->params['endDate']}";
        return [
            $row->date,
            $row->oper_id,
            $row->oper->name,
            '`'.$row->merchant_num,
            '`'.$row->user_num,
            '`'.$row->order_paid_num,
            '`'.$row->order_refund_num,
            '`'.$row->order_paid_amount,
            '`'.$row->order_refund_amount,
        ];
    }
}