<?php

namespace App\Exports;


use App\Modules\Bizer\BizerService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Modules\Wallet\WalletWithdraw;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletWithdrawExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $query;
    protected $originType;

    public function __construct($query, $originType = 0)
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
        if ($this->originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT){
            $array = [
                '提现时间',
                '提现编号',
                '提现金额',
                '手续费',
                '到账金额',
                '发票快递信息',
                '账户类型',
                '商户名称',
                '商户ID',
                '运营中心',
                '提现状态'
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_USER) {
            $array = [
                '提现时间',
                '提现编号',
                '提现金额',
                '手续费',
                '到账金额',
                '用户手机号码',
                '账户类型',
                '提现账号',
                '账户名',
                '开户行',
                '提现状态'
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_OPER) {
            $array = [
                '提现时间',
                '提现编号',
                '提现金额',
                '手续费',
                '到账金额',
                '发票快递信息',
                '账户类型',
                '运营中心名称',
                '运营中心ID',
                '提现状态'
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_BIZER) {
            $array = [
                '提现时间',
                '提现编号',
                '提现金额',
                '手续费',
                '到账金额',
                '业务员',
                '业务员手机号码',
                '账户类型',
                '提现状态'
            ];
        } else {
            // 批次明细 导出
            $array = [
                '提现时间',
                '提现编号',
                '批次编号',
                '批次类型',
                '提现类型',
                '提现账户名称',
                '银行卡号',
                '开户行',
                '提现金额',
                '手续费',
                '到账金额',
                '提现状态',
            ];
        }
        return $array;
    }

    public function map($row) : array
    {
        $invoiceExpress = '';

        if ($row->origin_type == WalletWithdraw::ORIGIN_TYPE_USER) {
            $user = UserService::getUserById($row->origin_id);
            $row->mobile = $user->mobile;
        } elseif ($row->origin_type == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
            $merchant = MerchantService::getById($row->origin_id);
            $row->merchant_name = $merchant->name;
            $row->oper_name = OperService::getNameById($merchant->oper_id);
            // 发票信息
            if ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_PEOPLE) {
                $invoiceExpress = '无需发票';
            } elseif ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_COMPANY) {
                $invoiceExpress = $row->invoice_express_company . '/' . $row->invoice_express_no;
            } else {
                $invoiceExpress = '未知';
            }
        } elseif ($row->origin_type == WalletWithdraw::ORIGIN_TYPE_OPER) {
            $row->oper_name = OperService::getNameById($row->origin_id);
            // 发票信息
            if ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_PEOPLE) {
                $invoiceExpress = '无需发票';
            } elseif ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_COMPANY) {
                $invoiceExpress = $row->invoice_express_company . '/' . $row->invoice_express_no;
            } else {
                $invoiceExpress = '未知';
            }
        } elseif ($row->origin_type == WalletWithdraw::ORIGIN_TYPE_BIZER) {
            $bizer = BizerService::getById($row->origin_id);
            $row->bizer_name = $bizer->name;
            $row->bizer_mobile = $bizer->mobile;
        }

        //账户类型
        if ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_COMPANY) {
            $bankCardType = '公司';
        } elseif ($row->bank_card_type == WalletWithdraw::BANK_CARD_TYPE_PEOPLE) {
            $bankCardType = '个人';
        } else {
            $bankCardType = '未知';
        }

        // 提现状态
        if ($row->status == WalletWithdraw::STATUS_AUDITING) {
            $status = '审核中';
        } elseif ($row->status == WalletWithdraw::STATUS_AUDIT) {
            $status = '审核通过';
        } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW) {
            $status = '已打款';
        } elseif ($row->status == WalletWithdraw::STATUS_WITHDRAW_FAILED) {
            $status = '打款失败';
        } elseif ($row->status == WalletWithdraw::STATUS_AUDIT_FAILED) {
            $status = '审核不通过';
        } else {
            $status = '未知';
        }

        if ($this->originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT){
            $array = [
                $row->created_at,
                $row->withdraw_no,
                $row->amount,
                $row->charge_amount,
                $row->remit_amount,
                $invoiceExpress,
                $bankCardType,
                $row->merchant_name,
                $row->origin_id,
                $row->oper_name,
                $status,
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_USER) {
            $array = [
                $row->created_at,
                $row->withdraw_no,
                $row->amount,
                $row->charge_amount,
                $row->remit_amount,
                $row->mobile,
                $bankCardType,
                substr($row->bank_card_no, 0, 5).'****'.substr($row->bank_card_no, -4),
                $row->bank_card_open_name,
                $row->bank_name,
                $status,
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_OPER) {
            $array = [
                $row->created_at,
                $row->withdraw_no,
                $row->amount,
                $row->charge_amount,
                $row->remit_amount,
                $invoiceExpress,
                $bankCardType,
                $row->oper_name,
                $row->origin_id,
                $status
            ];
        } elseif ($this->originType == WalletWithdraw::ORIGIN_TYPE_BIZER) {
            $array = [
                $row->created_at,
                $row->withdraw_no,
                $row->amount,
                $row->charge_amount,
                $row->remit_amount,
                $row->bizer_name,
                $row->bizer_mobile,
                $bankCardType,
                $status
            ];
        } else {
            // 批次明细 导出
            $array = [
                $row->created_at,
                $row->withdraw_no,
                $row->batch_no,
                ['', '对公', '对私'][$row->bank_card_type],
                ['', '用户提现', '商户提现', '运营中心提现', '业务员提现'][$row->origin_type],
                $row->bank_card_open_name,
                $row->bank_card_no,
                $row->bank_name,
                $row->amount,
                $row->charge_amount,
                $row->remit_amount,
                $status,
            ];
        }
        return $array;
    }
}