<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18
 * Time: 13:07
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Article\ArticleService;
use App\Modules\Setting\SettingService;
use App\Result;

class SettingController extends Controller
{

    /**
     * 小程序端商户共享系统配置详情
     */
    public function getList()
    {
        $settings = SettingService::get('merchant_share_in_miniprogram');
        return Result::success([
            'list' => $settings
        ]);
    }

    /**
     * 小程序端商户共享保存
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(){
        $data = request()->all([
            'merchant_share_in_miniprogram'
        ]);
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }

    /**
     * 获取积分规则配置列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCreditRulesList()
    {
        $list = SettingService::get('oper_profit_radio',
            'consume_quota_convert_ratio_to_parent',
            'credit_multiplier_of_amount',
            'user_level_1_of_credit_number',
            'user_level_2_of_credit_number',
            'user_level_3_of_credit_number',
            'user_level_4_of_credit_number',
            'merchant_level_1_of_invite_user_number',
            'merchant_level_2_of_invite_user_number',
            'merchant_level_3_of_invite_user_number',
            'credit_to_self_ratio_of_user_level_1',
            'credit_to_self_ratio_of_user_level_2',
            'credit_to_self_ratio_of_user_level_3',
            'credit_to_self_ratio_of_user_level_4',
            'credit_to_parent_ratio_of_user_level_2',
            'credit_to_parent_ratio_of_user_level_3',
            'credit_to_parent_ratio_of_user_level_4',
            'credit_multiplier_of_merchant_level_1',
            'credit_multiplier_of_merchant_level_2',
            'credit_multiplier_of_merchant_level_3');
        return Result::success([
            'list' => $list
        ]);
    }

    /**
     * 保存积分规则列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setCreditRules()
    {
        $data = request()->all([
            'oper_profit_radio',
            'consume_quota_convert_ratio_to_parent',
            'credit_multiplier_of_amount',
            'user_level_1_of_credit_number',
            'user_level_2_of_credit_number',
            'user_level_3_of_credit_number',
            'user_level_4_of_credit_number',
            'merchant_level_1_of_invite_user_number',
            'merchant_level_2_of_invite_user_number',
            'merchant_level_3_of_invite_user_number',
            'credit_to_self_ratio_of_user_level_1',
            'credit_to_self_ratio_of_user_level_2',
            'credit_to_self_ratio_of_user_level_3',
            'credit_to_self_ratio_of_user_level_4',
            'credit_to_parent_ratio_of_user_level_2',
            'credit_to_parent_ratio_of_user_level_3',
            'credit_to_parent_ratio_of_user_level_4',
            'credit_multiplier_of_merchant_level_1',
            'credit_multiplier_of_merchant_level_2',
            'credit_multiplier_of_merchant_level_3',
        ]);
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }

    /**
     * 系统设置 页面配置
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setArticle()
    {
        $this->validate(request(), [
            'code' => 'required'
        ]);
        $code = request('code');
        $article = ArticleService::editByCode($code, request('title', ''), request('content', ''));

        return Result::success($article);
    }

    /**
     * 获取文章
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getArticle()
    {
        $this->validate(request(), [
            'code' => 'required'
        ]);
        $code = request('code');
        $article = ArticleService::getByCode($code);
        return Result::success([
            'article' => $article,
        ]);
    }
}