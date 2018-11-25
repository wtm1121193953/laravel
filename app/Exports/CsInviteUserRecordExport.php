<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 9:50
 */

namespace App\Exports;


use App\Modules\Invite\InviteUserService;
use App\Support\Utils;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CsInviteUserRecordExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $cs_merchant_id;
    protected $mobile;

    public function __construct($cs_merchant_id, $mobile = '')
    {
        $this->cs_merchant_id = $cs_merchant_id;
        $this->mobile = $mobile;
    }

    public function query()
    {
        $query = InviteUserService::getInviteUsersWithOrderCountByCsMerchantId($this->cs_merchant_id, [
            'mobile' => $this->mobile
        ], true);

        return $query;
    }

    public function map($data) : array
    {
        return [
            $data->created_at,
            Utils::getHalfHideMobile($data->mobile),
            $data->wx_nick_name,
            $data->order_count,
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