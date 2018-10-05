<?php

namespace App\Exports;


use App\Modules\Wallet\WalletConsumeQuotaRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletConsumeQuotaRecordExport implements FromQuery, WithMapping, WithHeadings
{

    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function map($row) : array
    {
        /*if ($row->status == WalletConsumeQuotaRecord::STATUS_FREEZE) {
            $status = '冻结中';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_UNFREEZE) {
            $status = '已解冻待置换';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_REPLACEMENT) {
            $status = '已置换';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_REFUND) {
            $status = '已退款';
        } else {
            $status = '未知（'. $row->status .'）';
        }*/

        return [
            $row->created_at,
            $row->consume_quota_no,
            '被分享人贡献',
            $row->order_no,
            $row->consume_user_mobile,
            $row->pay_price,
            $row->consume_quota,
//            $status,
        ];
    }

    public function headings() : array
    {
        return [
            '交易时间',
            '交易号',
            '交易类型',
            '原订单号',
            '用户手机号',
            '交易金额',
            '贡献值',
//            '消费额状态',
        ];
    }
}