<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/22
 * Time: 22:05
 */

namespace App\Exports;


use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletWithdraw;
use App\Modules\Wallet\WalletWithdrawService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletBillExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;
    protected $originType;

    public function __construct($query, $originType)
    {
        $this->query = $query;
        $this->originType = $originType;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings() : array
    {
        if ($this->originType == WalletBill::ORIGIN_TYPE_USER) {
            $array = [
                '交易时间',
                '交易号',
                '用户手机号码',
                '交易类型',
                '账户交易金额',
                '账户余额',
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_MERCHANT) {
            $array = [
                '交易时间',
                '交易号',
                '商户名称',
                '商户等级',
                '交易类型',
                '账户交易金额',
                '账户余额',
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_OPER) {
            $array = [
                '交易时间',
                '交易号',
                '运营中心名称',
                '交易类型',
                '账户交易金额',
                '账户余额',
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_BIZER) {
            $array = [
                '交易时间',
                '交易号',
                '业务员手机号码',
                '业务员昵称',
                '交易类型',
                '账户交易金额',
                '账户余额'
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_CS) {
            $array = [
                '交易时间',
                '交易号',
                '商户名称',
                '交易类型',
                '账户交易金额',
                '账户余额',
            ];
        } else {
            $array = [];
        }
        return $array;
    }

    public function map($row) : array
    {
        if ($row->origin_type == WalletBill::ORIGIN_TYPE_MERCHANT) {
            $merchant = MerchantService::getById($row->origin_id, ['name', 'level']);
            $row->merchant_name = $merchant->name;
            $row->merchant_level = $merchant->level;
        }else if($row->origin_type == WalletBill::ORIGIN_TYPE_OPER){
            $row->oper_name = OperService::getNameById($row->origin_id);
        }else if ($row->origin_type == WalletBill::ORIGIN_TYPE_USER) {
            $row->user_mobile = UserService::getUserById($row->origin_id)->mobile;
        }else if ($row->origin_type == WalletBill::ORIGIN_TYPE_BIZER) {
            $bizer = BizerService::getById($row->origin_id);
            $row->bizer_mobile = $bizer->mobile;
            $row->bizer_name = $bizer->name;
        }else if ($row->origin_type == WalletBill::ORIGIN_TYPE_CS) {
            $merchant = CsMerchantService::getById($row->origin_id, ['name']);
            $row->merchant_name = $merchant->name;
        }

        if (in_array($row->type, [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED])) {
            $walletWithdraw = WalletWithdrawService::getWalletWithdrawById($row->obj_id);
            $row->status = $walletWithdraw->status;
        } else {
            $row->status = 0;
        }

        if ($row->type == WalletBill::TYPE_SELF) {
            $typeText = '自己消费奖励';
        } elseif ($row->type == WalletBill::TYPE_SUBORDINATE) {
            $typeText = '被分享人消费奖励';
        } elseif ($row->type == WalletBill::TYPE_SELF_CONSUME_REFUND) {
            $typeText = '自己消费奖励退款';
        } elseif ($row->type == WalletBill::TYPE_SUBORDINATE_REFUND) {
            $typeText = '被分享人消费奖励退款';
        } elseif ($row->type == WalletBill::TYPE_OPER) {
            $typeText = '交易分润入账';
        } elseif ($row->type == WalletBill::TYPE_OPER_REFUND) {
            $typeText = '交易分润退款';
        } elseif ($row->type == WalletBill::TYPE_WITHDRAW) {
            if ($row->status == WalletWithdraw::STATUS_AUDITING) {
                $typeText = '提现（审核中）';
            } elseif ($row->status == WalletWithdraw::STATUS_AUDIT) {
                $typeText = '提现（审核通过）';
            } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW) {
                $typeText = '提现（已打款）';
            } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW_FAILED) {
                $typeText = '提现（打款失败）';
            } elseif ($row->status == WalletWithdraw::STATUS_AUDIT_FAILED) {
                $typeText = '提现（审核不通过）';
            } else {
                $typeText = '提现（未知'.$row->status.'）';
            }
        } elseif ($row->type == WalletBill::TYPE_WITHDRAW_FAILED) {
            $typeText = '提现失败';
        } elseif ($row->type == WalletBill::TYPE_BIZER) {
            $typeText = '交易分润入账(业务员)';
        } elseif ($row->type == WalletBill::TYPE_BIZER_REFUND) {
            $typeText = '交易分润退款(业务员)';
        } else {
            $typeText = '未知'.$row->type;
        }

        if ($this->originType == WalletBill::ORIGIN_TYPE_USER) {
            $array = [
                $row->created_at,
                $row->bill_no,
                $row->user_mobile,
                $typeText,
                ($row->inout_type == 1 ? '+' : '-') . "\t" . $row->amount,
                $row->after_amount,
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_MERCHANT) {
            $array = [
                $row->created_at,
                $row->bill_no,
                $row->merchant_name,
                ['', '普通商户', '金牌商户', '超级商户'][$row->merchant_level],
                $typeText,
                ($row->inout_type == 1 ? '+' : '-') . "\t" . $row->amount,
                $row->after_amount,
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_OPER) {
            $array = [
                $row->created_at,
                $row->bill_no,
                $row->oper_name,
                $typeText,
                ($row->inout_type == 1 ? '+' : '-') . "\t" . $row->amount,
                $row->after_amount,
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_BIZER) {
            $array = [
                $row->created_at,
                $row->bill_no,
                $row->bizer_mobile,
                $row->bizer_name,
                $typeText,
                ($row->inout_type == 1 ? '+' : '-') . "\t" . $row->amount,
                $row->after_amount,
            ];
        } elseif ($this->originType == WalletBill::ORIGIN_TYPE_CS) {
            $array = [
                $row->created_at,
                $row->bill_no,
                $row->merchant_name,
                $typeText,
                ($row->inout_type == 1 ? '+' : '-') . "\t" . $row->amount,
                $row->after_amount,
            ];
        } else {
            $array = [];
        }
        return $array;
    }
}