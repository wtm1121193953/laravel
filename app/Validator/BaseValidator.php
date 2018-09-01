<?php

namespace App\Validator;

use Validator;

/**
 * Class BaseValidator
 * 在原有的验证基础上加一个场景判断
 * @package App\Validator
 * Author:  Jerry
 * Date:    180830
 */


class BaseValidator
{
    // 当前验证的规则
    protected $rule = [];

    // 验证提示信息
    protected $message = [];

    // 验证字段描述
    protected $field = [];

    // 验证场景 scene = ['edit'=>'name1,name2,...']
    protected $scene = [];

    // 当前验证场景
    protected $currentScene = null;

    /**
     * 构造函数
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $field 验证字段描述信息
     */
    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->rule    = array_merge($this->rule, $rules);
        $this->message = array_merge($this->message, $message);
        $this->field   = array_merge($this->field, $field);
    }

    /**
     * 设置验证场景
     * @access public
     * @param string|array  $name  场景名或者场景设置数组
     * @param mixed         $fields 要验证的字段
     * @return $this
     */
    public function scene($name, $fields = null)
    {
        if (is_array($name)) {
            $this->scene = array_merge($this->scene, $name);
        }if (is_null($fields)) {
        // 设置当前场景
        $this->currentScene = $name;
    } else {
        // 设置验证场景
        $this->scene[$name] = $fields;
    }
        return $this;
    }


    /**
     * @param array  $data  数据
     * @param array  $rules 验证规则
     * @param array  $messages 验证提示信息
     * @param string $scene 验证场景
     * @throws \Illuminate\Validation\ValidationException
     */
    public function check($data, $rules = [], $messages = [], $scene = '')
    {
        // 处理场景值
        if( empty($scene) && !is_null($this->currentScene) )
        {
            $scene = $this->currentScene;
        }
        // 处理提示信息
        if( empty($messages) && !empty($this->message) )
        {
            $messages = $this->message;
        }
        if( !empty( $scene ) && isset($this->scene[$scene]) )
        {
            $sceneRule = [];
            foreach ( $this->scene[$scene] as $k=>$v ){
                // 解决不同的场景值验证方式也不同
                if( is_integer( $k ) )
                {
                    $sceneRule[$v] = $this->rule[$v];
                }else{
                    $sceneRule[$k] = $v;
                }
            }
            $rules = $sceneRule;
        }
        if( empty($rules) )
        {
            $rules = $this->rule;
        }
        Validator::validate( $data, $rules, $messages );
    }

}