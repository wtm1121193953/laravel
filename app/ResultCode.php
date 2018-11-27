<?php
/**
 * User: Evan Lee
 * Date: 2017/6/20
 * Time: 18:51
 */

namespace App;


class ResultCode
{
    const SUCCESS = 0;


    const UNKNOWN = 500; // 未知错误

    const API_NOT_FOUND = 10000; // 接口不存在
    const PARAMS_INVALID = 10001; // 参数不合法
    const NO_PERMISSION = 10002; // 无权限操作

    const UNLOGIN = 10003;  // 未登录
    const ACCOUNT_NOT_FOUND = 10004; // 账号不存在
    const ACCOUNT_PASSWORD_ERROR = 10005;  // 账号密码错误
    const ACCOUNT_EXISTS = 10006;  // 账号已存在
    const TOKEN_INVALID = 10007;  // token无效
    const USER_ALREADY_BEEN_INVITE = 10008; // 用户已经被邀请过

    const DB_QUERY_FAIL = 10010;  // 数据库查询失败
    const DB_INSERT_FAIL = 10011;   // 数据库插入失败
    const DB_UPDATE_FAIL = 10012;  // 数据库更新失败
    const DB_DELETE_FAIL = 10013;  // 数据库删除失败


    const RULE_NOT_FOUND        = 10021;        // 权限不存在
    const AUTH_GROUP_NOT_FOUND  = 10022;        // 用户组不存在
    const RULE_SORT_DUPLICATED  = 10023;        // 权限排序不能重复
    const PERMISSION_NOT_ALLOWED = 10025;       // 无权限进行该操作

    const TSP_DB_READONLY = 10051; // tsp数据库只读
    const TSP_REQUEST_FAIL = 10052; // 请求tsp系统失败

    const UPLOAD_ERROR = 20001;   // 文件上传失败

    const SMS_SEND_ERROR = 30001;   // 短信发送失败
    const SMS_BUSINESS_LIMIT_CONTROL = 30015;  // 短信发送频率超过限制

    const EMAIL_SEND_ERROR = 31000; // 邮件发送失败

    const CS_MERCHANT_OFF = 40001; // 超市商户禁用
    const CS_GOODS_STOCK_NULL = 40002; // 超市商品库存不足
    const CS_MERCHANT_NOT_EXIST = 40003; // 超市商户不存在


    /************ 微信接口相关  **************/
    const WECHAT_REQUEST_FAIL          = 70001;   // 请求微信服务器失败
    const WECHAT_CERT_NOT_FOUND        = 70002; // 车企微信公众号支付证书不存在
    const WECHAT_CERT_NOT_READABLE     = 70003; // 车企微信公众号支付证书不可读
    const WECHAT_CERT_KEY_NOT_FOUND    = 70004; // 车企微信公众号支付证书密钥不存在
    const WECHAT_CERT_KEY_NOT_READABLE = 70005; // 车企微信公众号支付证书密钥不可读

    const WECHAT_OPEN_ID_NOT_FOUND = 70010; // 微信openId不存在
    const WECHAT_APPID_INVALID = 70011; // 微信appid不存在
    const MINIPROGRAM_PAGE_NOT_EXIST = 70020; // 微信小程序页面不存在
    const MINIPROGRAM_CONFIG_NOT_EXIST = 70021; // 微信小程序配置不存在
    const MINIPROGRAM_CONFIG_ALREADY_EXIST = 70022; // 微信小程序配置已存在
}