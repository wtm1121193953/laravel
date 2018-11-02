<?php

namespace App\Exports;
use App\Exceptions\BaseResponseException;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 营销报表导出
 * Class OperInviteRecordsExport
 * @package App\Jobs
 */
class StatisticsExport implements FromQuery, WithHeadings, WithMapping
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
        if ($this->params['steType'] == 3) {
            return [
                '时间',
                '运营中心id',
                '运营中心名称',
                '运营中心省份+城市',
                '运营中心邀请会员数',
                '商户邀请会员数',
                '运营中心及商户共邀请会员数',
                '商户总数',
                '正式商户数',
                '试点商户数',
                '总金额(已完成)/笔数',
            ];
        } elseif ($this->params['steType'] == 2) {
            return [
                '时间',
                '商户ID',
                '商户名称',
                '商户省份+城市',
                '商户邀请会员数',
                '总金额(已完成)/笔数',
            ];
        } elseif ($this->params['steType'] == 1) {
            return [
                '时间',
                '用户ID',
                '用户手机号码',
                '邀请会员数',
                '总金额(已完成)/笔数',
            ];
        } else {
            throw new BaseResponseException('导出类型不存在');
        }
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $row->date = "{$this->params['startDate']}至{$this->params['endDate']}";
        if ($this->params['steType'] == 3) {
            return [
                $row->date,
                $row->oper_id,
                $row->oper->name,
                $row->oper->province .'/'. $row->oper->city,
                '`'.$row->user_num,
                '`'.$row->merchant_invite_num,
                '`'.$row->oper_and_merchant_invite_num,
                '`'.$row->merchant_total_num,
                '`'.$row->merchant_num,
                '`'.$row->merchant_pilot_num,
                '`'.$row->order_paid_amount .'/'. $row->order_paid_num
            ];
        } elseif ($this->params['steType'] == 2) {
            return [
                $row->date,
                $row->merchant_id,
                $row->merchant->name,
                $row->merchant->province .'/'. $row->merchant->city,
                '`'.$row->invite_user_num,
                '`'.$row->order_finished_amount .'/'. $row->order_finished_num
            ];
        } elseif ($this->params['steType'] == 1) {
            return [
                $row->date,
                $row->user_id,
                $row->user->mobile,
                '`'.$row->invite_user_num,
                '`'.$row->order_finished_amount .'/'. $row->order_finished_num
            ];
        } else {
            throw new BaseResponseException('导出类型不存在');
        }
    }
}