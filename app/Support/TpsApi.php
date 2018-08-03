<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/3
 * Time: 14:15
 */

namespace App\Support;


class TpsApi
{

    public static function sendEmail($to, $title, $content)
    {
        // todo 发送邮件
    }

    /**
     * 创建账号
     * @param $account string 账号
     * @param $password string 密码
     * @param string $parentAccount 父级账号, 运营中心创建账号时为空
     * @param int $type 账号类型 1-运营中心账号  2- 商户创建
     */
    public static function createTpsAccount($account, $password, $parentAccount='', $type=1)
    {
        // todo
    }

    /**
     * 验证账号是否正确
     * @param $account
     * @param $password
     */
    public static function checkTpsAccount($account, $password)
    {
        // todo
    }


}