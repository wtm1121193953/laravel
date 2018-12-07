<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/8
 * Time: 15:25
 */

namespace App\Exports;
use App\Modules\Platform\PlatformTradeRecord;
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
class PlatformTradeRecordsExport implements FromQuery, WithHeadings, WithMapping
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
            '交易时间',
            '交易流水',
            '订单编号',
            '交易金额¥',
            '交易类型',
            '交易商户',
            '所属运营中心',
            '用户ID',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $types = PlatformTradeRecord::getAllType();
        if ($row->type == 2) {
            $row->trade_amount = '-' . $row->trade_amount;
        }
        return [
            $row->trade_time,
            $row->trade_no,
            $row->order_no,
            $row->trade_amount,
            $types[$row->type],
            $row->merchant ? $row->merchant->name : ($row->cs_merchant ? $row->cs_merchant->name : ''),
            $row->oper->name,
            $row->user_id,
        ];
    }
}