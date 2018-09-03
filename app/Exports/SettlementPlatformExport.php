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
            '结算商户',
            '运营中心',
            '银行账号',
            '开户行',
            '账户名',
            '结算时间',
            '结算订单日期',
            '订单金额',
            '利率',
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
            $row->merchant->name,
            $row->oper->name,
            $row->bank_card_no,
            $row->sub_bank_name,
            $row->bank_open_name,
            $row->created_at,
            $row->date,
            $row->amount,
            $row->settlement_rate,
            $row->real_amount,
            SettlementPlatformService::$status_vals[$row->status]
        ];
    }
}