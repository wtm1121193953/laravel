<?php

namespace App\Exports;


use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Modules\Wallet\BankCardService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletExport implements FromQuery, WithMapping, WithHeadings
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
        if ($this->originType == Wallet::ORIGIN_TYPE_USER) {
            $array = [
                '用户手机号码',
                '用户ID',
                '账户余额',
                '可提现金额',
                '冻结金额',
//                '银行账号',
//                '账户名',
//                '开户行',
                '账户状态',
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_MERCHANT || $this->originType == Wallet::ORIGIN_TYPE_CS) {
            $array = [
                '商户名称',
                '商户ID',
                '账户余额',
                '可提现金额',
                '冻结金额',
                '银行账号',
                '账户名',
                '开户行',
                '运营中心',
                '账户状态',
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_OPER) {
            $array = [
                '运营中心',
                '运营中心ID',
                '账户余额',
                '可提现金额',
                '冻结金额',
                '银行账号',
                '账户名',
                '开户行',
                '账户状态',
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_BIZER) {
            $array = [
                '业务员手机号码',
                '业务员昵称',
                '业务员ID',
                '账户余额',
                '可提现金额',
                '冻结金额',
                '银行账号',
                '账户名',
                '开户行',
                '账户状态',
            ];
        } else {
            $array = [];
        }
        return $array;
    }

    public function map($row) : array
    {
        if ($row->origin_type == Wallet::ORIGIN_TYPE_USER) {
            $user = UserService::getUserById($row->origin_id);
            $bankCard = WalletService::getBankCardByOriginInfo($row->origin_id, $row->origin_type);
            $row->user_mobile = $user->mobile;
            $row->bank_open_name = isset($bankCard->bank_card_open_name) ?: '';
            $row->bank_card_no = isset($bankCard->bank_card_no) ?: '';
            $row->sub_bank_name = isset($bankCard->bank_name) ?: '';
            $row->bank_card_type = isset($bankCard->bank_card_type) ?: 0;
        } elseif ($row->origin_type == Wallet::ORIGIN_TYPE_MERCHANT) {
            $merchant = MerchantService::getById($row->origin_id);
            $row->merchant_name = $merchant->name;
            $row->oper_name = OperService::getNameById($merchant->oper_id);
            $row->bank_open_name = $merchant->bank_open_name;
            $row->bank_card_no = $merchant->bank_card_no;
            $row->sub_bank_name = $merchant->sub_bank_name;
            $row->bank_card_type = $merchant->bank_card_type;
        } elseif ($row->origin_type == Wallet::ORIGIN_TYPE_OPER) {
            $oper = OperService::getById($row->origin_id);
            $row->oper_name = $oper->name;
            $row->bank_open_name = $oper->bank_open_name;
            $row->bank_card_no = $oper->bank_card_no;
            $row->sub_bank_name = $oper->sub_bank_name;
        } elseif ($row->origin_type == Wallet::ORIGIN_TYPE_BIZER) {
            $bizer = BizerService::getById($row->origin_id);
            $row->bizer_name = $bizer->name;
            $row->bizer_mobile = $bizer->mobile;
            $bizerBank = BankCardService::getBankCardByOriginInfo($row->origin_id, $row->origin_type);
            $row->bank_open_name = $bizerBank->bank_card_open_name ?? '';
            $row->bank_card_no = $bizerBank->bank_card_no ?? '';
            $row->bank_name = $bizerBank->bank_name ?? '';
            $row->bank_card_type = $bizerBank->bank_card_type ?? '';
        } elseif ($row->origin_type == Wallet::ORIGIN_TYPE_CS) {
            $merchant = CsMerchantService::getById($row->origin_id);
            $row->merchant_name = isset($merchant->name) ? $merchant->name : '';
            $row->oper_name = isset($merchant->oper_id) ? OperService::getNameById($merchant->oper_id) : '';
            $row->bank_open_name = $merchant->bank_open_name;
            $row->bank_card_no = $merchant->bank_card_no;
            $row->sub_bank_name = $merchant->sub_bank_name;
            $row->bank_card_type = $merchant->bank_card_type;
        }

        if ($this->originType == Wallet::ORIGIN_TYPE_USER) {
            $array = [
                $row->user_mobile,
                $row->origin_id,
                $row->balance + $row->freeze_balance,
                $row->balance,
                $row->freeze_balance,
//                $row->bank_card_no ? substr($row->bank_card_no, 0, 5).'****'.substr($row->bank_card_no, -4).'('.['','企业','个人'][$row->bank_card_type].')' : '',
//                $row->bank_open_name,
//                $row->bank_name,
                ['', '正常', '已冻结'][$row->status]
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_MERCHANT || $this->originType == Wallet::ORIGIN_TYPE_CS) {
            $array = [
                $row->merchant_name,
                $row->origin_id,
                $row->balance + $row->freeze_balance,
                $row->balance,
                $row->freeze_balance,
                $row->bank_card_no ? substr($row->bank_card_no, 0, 5).'****'.substr($row->bank_card_no, -4).'('.['','企业','个人'][$row->bank_card_type].')' : '',
                $row->bank_open_name,
                $row->sub_bank_name,
                $row->oper_name,
                ['', '正常', '已冻结'][$row->status]
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_OPER) {
            $array = [
                $row->oper_name,
                $row->origin_id,
                $row->balance + $row->freeze_balance,
                $row->balance,
                $row->freeze_balance,
                $row->bank_card_no ? substr($row->bank_card_no, 0, 5).'****'.substr($row->bank_card_no, -4).'(企业)' : '',
                $row->bank_open_name,
                $row->sub_bank_name,
                ['', '正常', '已冻结'][$row->status]
            ];
        } elseif ($this->originType == Wallet::ORIGIN_TYPE_BIZER) {
            $array = [
                $row->bizer_mobile,
                $row->bizer_name,
                $row->origin_id,
                $row->balance + $row->freeze_balance,
                $row->balance,
                $row->freeze_balance,
                $row->bank_card_no ? substr($row->bank_card_no, 0, 5).'****'.substr($row->bank_card_no, -4).'(企业)' : '',
                $row->bank_open_name,
                $row->bank_name,
                ['', '正常', '已冻结'][$row->status]
            ];
        } else {
            $array = [];
        }
        return $array;
    }
}