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
            '商户名称',
            '运营中心',
            '结算周期',
            '结算单生成时间',
            '结算日期',
            '类型',
            '银行开户名',
            '银行账号',
            '开户行',
            '开户行网点名称',
            '开户行网点地址',
            '订单金额',
            '结算金额',
            '结算状态',
            '备注'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $bank_info = explode('|',$row->sub_bank_name);
        $c = count($bank_info);
        $bank_name = '';
        if ($c == 1) {
            $sub_bank_name = $bank_info[0];
        } else {
            $bank_name = $bank_info[0];
            $sub_bank_name = $bank_info[1];
        }
        return [
            $row->merchant? $row->merchant->id:($row->cs_merchant ? $row->cs_merchant->id : 0),
            $row->merchant? $row->merchant->name:($row->cs_merchant?$row->cs_merchant->name : ''),
            $row->oper->name??'',
            ['', '周结', '半月结', 'T+1(自动)', '半年结', '年结', 'T+1(人工)', '未知'][$row->settlement_cycle_type],
            $row->created_at,
            $row->start_date.'至'.$row->end_date,
            $row->bank_card_type == 1?'公司':'个人',
            $row->bank_open_name,
            ' ' . $row->bank_card_no,
            $bank_name,
            $sub_bank_name,
            $row->bank_open_address,
            $row->amount,
            $row->real_amount,
            SettlementPlatformService::$status_vals[$row->status],
            $row->reason
        ];
    }
}