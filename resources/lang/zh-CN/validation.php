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
        'verifyCode' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
        ],
        'captcha' => [
            'required' => '请输入验证码',
            'captcha' => '验证码不正确',
        ],
        'name' => [
            'required' => '名称不能为空',
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
