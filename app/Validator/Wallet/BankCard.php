<?php
namespace App\Validator\Wallet;
use App\Validator\BaseValidator;

/**
 * Class BankCard
 * @package App\Validator\Wallet
 * Author:  Jerry
 * Date:    180830
 * 银行验证类
 */
class BankCard extends BaseValidator{
    protected $rule = [
        'id'                    =>  'required|exists:bank_cards,id',
        'bank_card_no'          =>  'bail|required|numeric|unique:bank_cards',
        'bank_card_open_name'   =>  'required',
        'bank_name'             =>  'required',
        'status'                =>  'required'
    ];

    protected $message = [
        'bank_card_open_name.required'  => '持卡人不可为空',
        'bank_card_no.required'         => '银行卡号不可为空',
        'bank_name.required'            => '银行不可为空',
        'bank_card_no.numeric'          => '银行卡号只能是数字',
        'bank_card_no.size'             => '银行卡只能为19位数',
        'bank_card_no.unique'           => '银行卡号已存在，不可重复',
        'id'                            => '银行卡信息有误'
    ];

    protected $scene = [
        'add'       =>  [ 'bank_card_no', 'bank_card_open_name', 'bank_name'],
        'default'   =>  [ 'id' ],
    ];
}