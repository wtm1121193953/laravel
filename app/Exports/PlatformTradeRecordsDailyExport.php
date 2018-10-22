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
class PlatformTradeRecordsDailyExport implements FromQuery, WithHeadings, WithMapping
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
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            '交易日',
            '实收金额/笔数',
            '退款金额/笔数',
            '收益',
            '统计时间',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $row->pays = "{$row->pay_amount}元/{$row->pay_count}笔";
        $row->refunds = "{$row->refund_amount}元/{$row->refund_count}笔";
        $row->income = ($row->pay_amount - $row->refund_amount) . '元';
        return [
            $row->sum_date,
            $row->pays,
            $row->refunds,
            $row->income,
            $row->updated_at,
        ];
    }
}