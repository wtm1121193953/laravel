<?php
namespace App\Validator\User;
use App\Validator\BaseValidator;

/**
 * Class UserIdentityAuditRecord
 * @package App\Validator\Wallet
 * Author:  Jerry
 * Date:    180831
 * 身份验证类
 */
class UserIdentityAuditRecord extends BaseValidator{
    protected $rule = [
        'id'            => 'required',
        'name'          => 'required',
        'number'        => 'required|identitycards|unique:user_identity_audit_records',
        'front_pic'     => 'required',
        'opposite_pic'  => 'required',
        'user_id'       => 'unique:user_identity_audit_records'
    ];

    protected $message = [
        'name.required'         => '姓名不可为空',
        'number.required'       => '身份证不可为空',
        'number.identitycards'  => '身份证号码格式错误',
        'front_pic.required'    => '身份证正面照不可缺',
        'opposite_pic.required' => '身份证反面不可缺',
        'number.unique'         => '身份证号码已验证过',
        'user_id.unique'        => '不可重复提交'
    ];

    protected $scene = [
        'add'       =>  [ 'name', 'number', 'front_pic', 'opposite_pic', 'user_id'],
        'mod'       =>  [ 'id',  'number'=>'identitycards' ],
    ];
}