<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 9:50
 */

namespace App\Exports;


use App\Modules\Invite\InviteStatisticsService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InviteUserRecordExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $merchantId;
    protected $mobile;

    public function __construct($merchantId, $mobile = '')
    {
        $this->merchantId = $merchantId;
        $this->mobile = $mobile;
    }

    public function query()
    {
        $merchantId = $this->merchantId;
        $mobile = $this->mobile;

        $query = InviteStatisticsService::getInviteRecordListByMerchantId($merchantId, '', $mobile, true);

        return $query;
    }

    public function map($data) : array
    {
        return [
            $data->created_at,
            $data->mobile,
            $data->nick_name,
            $data->order_number,
        ];
    }

    public function headings() : array
    {
        return [
            '注册日期',
            '手机号码',
            '微信昵称',
            '下单次数',
        ];
    }
}