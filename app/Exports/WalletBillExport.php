<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/22
 * Time: 22:05
 */

namespace App\Exports;


use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletWithdraw;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletBillExport implements FromQuery, WithHeadings, WithMapping
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

    public function headings() : array
    {
        return [
            '交易时间',
            '交易号',
            '商户名称',
            '交易类型',
            '账户交易金额',
            '账户余额',
        ];
    }

    public function map($row) : array
    {
        if ($row->type == WalletBill::TYPE_SUBORDINATE) {
            $typeText = '用户消费返利';
        } elseif ($row->type == WalletBill::TYPE_SUBORDINATE_REFUND) {
            $typeText = '用户消费返利退款';
        } elseif ($row->type = WalletBill::TYPE_WITHDRAW) {
            if ($row->status == WalletWithdraw::STATUS_WITHDRAWING) {
                $typeText = '提现（提现中）';
            } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW_SUCCESS) {
                $typeText = '提现（提现成功）';
            } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW_FAILED) {
                $typeText = '提现（提现失败）';
            } else {
                $typeText = '提现（未知'.$row->status.'）';
            }
        } elseif ($row->type == WalletBill::TYPE_WITHDRAW_FAILED) {
            $typeText = '提现失败';
        } else {
            $typeText = '未知'.$row->type;
        }

        return [
            $row->created_at,
            $row->bill_no,
            $row->merchant_name,
            $typeText,
            $row->amount,
            $row->after_amount,
        ];
    }
}