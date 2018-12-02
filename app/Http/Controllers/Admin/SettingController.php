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
        $list = SettingService::get(
            'fee_splitting_ratio_to_self',
            'fee_splitting_ratio_to_parent_of_user',
            'fee_splitting_ratio_to_parent_of_merchant_level_1',
            'fee_splitting_ratio_to_parent_of_merchant_level_2',
            'fee_splitting_ratio_to_parent_of_merchant_level_3',
            'fee_splitting_ratio_to_parent_of_oper',
            'supermarket_on',
            'supermarket_city_limit',
            'supermarket_show_city_limit',
            'supermarket_index_cs_banner_on'
        );
        $list['supermarket_on'] = isset($list['supermarket_on'])?intval($list['supermarket_on']):0;
        $list['supermarket_city_limit'] = isset($list['supermarket_city_limit'])?intval($list['supermarket_city_limit']):0;
        $list['supermarket_show_city_limit'] = isset($list['supermarket_show_city_limit'])?intval($list['supermarket_show_city_limit']):0;
        $list['supermarket_index_cs_banner_on'] = isset($list['supermarket_index_cs_banner_on'])?intval($list['supermarket_index_cs_banner_on']):0;
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
            'fee_splitting_ratio_to_self',
            'fee_splitting_ratio_to_parent_of_user',
            'fee_splitting_ratio_to_parent_of_merchant_level_1',
            'fee_splitting_ratio_to_parent_of_merchant_level_2',
            'fee_splitting_ratio_to_parent_of_merchant_level_3',
            'fee_splitting_ratio_to_parent_of_oper',
            'supermarket_on',
            'supermarket_city_limit',
            'supermarket_show_city_limit',
            'supermarket_index_cs_banner_on'
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


    /**
     * 获取商户端电子合同开关 设置
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMerchantElectronicContractList()
    {
        $settings = SettingService::get('merchant_electronic_contract');
        return Result::success([
            'list' => $settings
        ]);
    }

    /**
     * 设置 商户端电子合同开关
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setMerchantElectronicContract(){
        $data = request()->all([
            'merchant_electronic_contract'
        ]);
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }
}