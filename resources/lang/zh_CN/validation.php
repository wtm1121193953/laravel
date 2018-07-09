<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 表单验证错误信息 - 中文 (只包含自定义的部分, 框架原有的直接使用英文)
    |--------------------------------------------------------------------------
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'id' => [
            'required' => 'ID不能为空',
            'numeric' => 'ID只能是数字',
            'integer' => 'ID只能是整数',
            'min' => 'ID只能是大于0的整数'
        ],
        'projectId' => [
            'required' => '项目ID不能为空',
            'numeric' => '项目ID只能是数字',
            'integer' => '项目ID只能是整数',
            'min' => '项目ID只能是大于0的整数'
        ],
        'username' => [
            'required' => '请输入用户名',
        ],
        'email' => [
            'required' => '请输入邮箱',
            'email' => '请填写正确格式的邮箱',
        ],
        'password' => [
            'required' => '请输入密码',
            'between' => '请输入 :min 到 :max 位的密码',
            'min' => '密码不能少于 :min 位',
            'max' => '密码不能多于 :max 位',
        ],
        'newPassword' => [
            'required' => '请输入新密码',
        ],
        'reNewPassword' => [
            'required' => '请确认新密码',
            'same' => '两次输入的密码不相符'
        ],
        'verifyCode' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
            'size' => '验证码位数不正确',
        ],
        'captcha' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
        ],
        'name' => [
            'required' => '名称不能为空',
        ],
        'status' => [
            'required' => '状态不能为空',
            'numeric' => '状态只能是数字',
            'integer' => '状态只能是整数',
        ],


        'base_url' => [
            'required' => '根地址不能为空',
            'url' => '根地址格式不正确',
        ],
        'requests' => [
            'array' => '请求参数必须是数组',
        ],
        'responses' => [
            'array' => '请求参数必须是数组',
        ],
        'merchant_category_id' => [
            'required' => '所属行业不能为空',
        ],
        'business_licence_pic_url' => [
            'required' => '营业执照不能为空',
        ],
        'market_price' => [
            'required' => '市场价格不能为空',
        ],
        'price' => [
            'required' => '销售价格不能为空',
            'min' => '价格需大于 :min',
            'numeric' => '价格必须为数字',
        ],
        'merchant_id' => [
            'required' => '商户ID不能为空',
            'integer' => '商户ID必须为整数',
            'min' => '商户ID不能小于 :min',
        ],

        'settlement_rate' => [
            'required' => '结算费率不能为空',
            'numeric' => '结算费率只能是数字',
            'min' => '结算费率不能低于百分之 :min'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
