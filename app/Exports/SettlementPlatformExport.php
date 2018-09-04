<?php
/**
 *
 */

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Modules\Settlement\SettlementPlatformService;

class SettlementPlatformExport implements FromQuery, WithHeadings, WithMapping
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
            '商户ID',
            '结算商户',
            '结算单生成时间',
            '结算周期',
            '运营中心',
            '银行账号',
            '开户行',
            '账户名',
            '订单金额',
            '结算金额',
            '状态'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {

        return [
            $row->merchant->id,
            $row->merchant->name,
            $row->created_at,
            $row->start_date.'至'.$row->end_date,
            $row->oper->name,
            '`' . $row->bank_card_no,
            $row->sub_bank_name,
            $row->bank_open_name,
            $row->amount,
            $row->real_amount,
            SettlementPlatformService::$status_vals[$row->status]
        ];
    }
}