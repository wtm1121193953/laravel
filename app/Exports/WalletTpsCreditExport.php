<?php

namespace App\Exports;


use App\Modules\Wallet\WalletConsumeQuotaRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletTpsCreditExport implements FromQuery, WithHeadings, WithMapping
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
        if ($row->status == WalletConsumeQuotaRecord::STATUS_FREEZE) {
            $status = '冻结中';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_UNFREEZE) {
            $status = '已解冻待置换';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_REPLACEMENT) {
            $status = '已置换';
        } elseif ($row->status == WalletConsumeQuotaRecord::STATUS_REFUND) {
            $status = '已退款';
        } else {
            $status = '未知（'. $row->status .'）';
        }

        return [
            $row->created_at,
            $row->consume_quota_no,
            '被分享人贡献',
            $row->consume_user_mobile,
            $row->sync_tps_credit,
            $status,
        ];
    }

    public function headings() : array
    {
        return [
            '交易时间',
            '流水号',
            '交易类型',
            '用户手机号',
            '贡献积分',
            '积分状态',
        ];
    }
}