<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 15:58
 */

namespace App\Exports;


use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperBizMember;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperSettlementExport implements FromQuery, WithHeadings, WithMapping
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
            '结算商户',
            '结算时间',
            '结算周期',
            '订单金额',
            '费率',
            '结算金额',
            '结算状态',
            '签约人',
        ];
    }

    /**
     * @param mixed $row
     *Merchant::where('oper_id', $row->oper_id)->value('name'),
     * @return array
     */
    public function map($row): array
    {
        if ($row->bizer_name) {
            $signName = '[业务员]' . $row->bizer_name . '/' . $row->bizer_mobile;
        } elseif ($row->oper_biz_member_name) {
            $signName = '[员工]' . $row->oper_biz_member_name . '/' . $row->oper_biz_member_mobile;
        } else {
            $signName = '无';
        }

        return [
            $row->id,
            $row->merchant_name,
            $row->settlement_date,
            $row->start_date.'至'.$row->end_date,
            $row-> amount,
            $row->settlement_rate.'%',
            $row->real_amount,
            $row->status ==1 ? '审核中':'已打款',
            $signName,
        ];
    }
}