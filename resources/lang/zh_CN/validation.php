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
            'numeric' => '密码必须为数字'
        ],

        'newPassword' => [
            'required' => '请输入新密码',
            'between' => '请输入 :min 到 :max 位的新密码',
            'min' => '密码不能少于 :min 位',
            'max' => '密码不能多于 :max 位',
        ],
        'reNewPassword' => [
            'required' => '请确认新密码',
            'same' => '两次输入的密码不相符'
        ],
        'confirmPassword' => [
            'required' => '请确认密码',
            'same' => '两次输入的密码不相符'
        ],
        'verifyCode' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
            'size' => '验证码位数不正确',
        ],

        'verify_code' => [
            'required' => '请输入手机验证码',
            'size' => '验证码位数为:size位',
            'min' => '验证码位数最少为:min位',
        ],

        'captcha' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
        ],
        'name' => [
            'required' => '名称不能为空',
            'max' => '名称不能超过 :max字',

        ],
        'status' => [
            'required' => '状态不能为空',
            'numeric' => '状态只能是数字',
            'integer' => '状态只能是整数',
        ],
        'audit_suggestion' => [
            'max' => '最多输入 :max 字',
        ],

        'account' => [
            'required' => '帐户名不能为空',
        ],

        'mobile' => [
            'required' => '手机号码不能为空',
            'size' => '手机号码位数不正确',
            'regex' => '手机号码不合法'
        ],

        'contact_phone' => [
            'required' => '手机号码不能为空',
            'size' => '手机号码位数不正确',
            'regex' => '手机号码格式错误'
        ],
        'contacts' => [
            'max' => '最多输入 :max 字',
            'min' => '最多输少 :min 字',
            'regex' => '联系人必须为中文'
        ],

        'province_id' => [
            'required' => '省份ID不能为空',
        ],

        'city_id' => [
            'required' => '城市ID不能为空',
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
        'signboard_name' => [
            'required' => '商户招牌名不能为空',
            'max' => '商户招牌名不能超过 :max字'
        ],
        'organization_code' => [
            'required' => '营业执照代码不能为空',
        ],

        'settlement_rate' => [
            'required' => '结算费率不能为空',
            'numeric' => '结算费率只能是数字',
            'min' => '结算费率不能低于百分之 :min'
        ],
        'user_id'   =>  [
            'unique'    =>  '不可重复提交'
        ],
        'country_id'   =>  [
            'required'    =>  '国别不能为空'
        ],
        'id_card_no'    =>  [
            'unique'        =>  '身份证号码已验证过',
            'identitycards' =>  '身份证号码格式错误',
            'required'      =>  '身份证不可为空',
            'min'           =>  '身份证号码长度不正确',
        ],
        'front_pic'   =>  [
            'required'    =>  '身份证正面照不可缺'
        ],
        'opposite_pic'   =>  [
            'required'    =>  '身份证反面不可缺'
        ],
        'bank_card_open_name'   =>  [
            'required'  =>  '持卡人不可为空',
            'max'       =>  '银行名不可超过 :max个字'
        ],
        'bank_card_no'      =>  [
            'required'  =>  '银行卡号不可为空',
            'numeric'   =>  '银行卡号只能是数字',
            'max'       =>  '银行卡不能大于:max位数',
            'min'       =>  '银行卡不能小于:min位数',
            'unique'    =>  '银行卡号已存在，不可重复'
        ],
        'bank_name'     =>  [
            'required'  =>  '银行不可为空',
            'max'       =>  '银行名不可超过 :max个字'
        ],
        'sceneId'  => [
            'required' => '场景ID不能为空',
            'integer' => '场景ID必须为整数',
            'min' => '场景ID不能小于0',
        ],
        'view_name' =>  [
            'required'  =>  '前端展示名称'
        ],
        'pay_type'  =>  [
            'required'  =>  '支付类型不可为空',
            'integer'   =>  '支付类型必须为整数'
        ],
        'temp_token'    =>  [
            'required'  =>  '交易密码不正确'
        ],
        'contact_wechat'    =>  [
            'required'  =>  '联系微信不可为空',
            'regex'     =>  '联系微信号必须由5~19位英文字母、数字与_组成的'
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
