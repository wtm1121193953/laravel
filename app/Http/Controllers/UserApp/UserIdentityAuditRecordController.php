<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/6
 * Time: 下午2:50
 */
namespace App\Http\Controllers\UserApp;

use App\Exceptions\ParamInvalidException;
use App\Modules\Country\Country;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\User\UserIdentityAuditRecordService;

/**
 * 验证记录
 * Author:  zwg
 * Date:    180906
 * Class UserIdentityAuditRecord
 * @package App\Http\Controllers\UserApp
 */
class UserIdentityAuditRecordController extends Controller
{

    /**
     * Author:  zwg
     * Date:    180906
     * 新增身份验证记录
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addRecord(Request $request)
    {

        // 注入user_id
        $request->offsetSet('user_id', request()->get('current_user')->id);
        $request->validate([
            'name' => 'required',
            'country_id'    => 'required',
            'id_card_no' => 'bail|required',
            'front_pic' => 'required',
            'opposite_pic' => 'required',
            'user_id' => 'unique:user_identity_audit_records'
        ]);

        $countryId = $request->get('country_id');
        $idCardNo = $request->get('id_card_no');

        if($countryId == 2){
            $reg = '/(^((\s?[A-Za-z])|([A-Za-z]{2}))\d{6}\((([0−9aA])|([0-9aA]))\)$)/';
        }elseif ($countryId == 3){
            $reg = '/^[1|5|7][0-9]{6}\([0-9]\)/';
        }elseif ($countryId == 4){
            $reg = '/^[a-zA-Z][0-9]{9}$/';
        }else{
            $reg = '/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/';
        }
        if(!preg_match($reg,$idCardNo)){
            throw new ParamInvalidException('请输入正确的身份证号码');
        }
        $saveData = [
            'name' => $request->get('name'),
            'country_id' => $request->get('country_id'),
            'id_card_no' => $request->get('id_card_no'),
            'front_pic' => $request->get('front_pic'),
            'opposite_pic' => $request->get('opposite_pic'),
        ];
        UserIdentityAuditRecordService::addRecord($saveData, request()->get('current_user'));
        return Result::success('提交成功');
    }

    /**
     * Author:  zwg
     * Date:    180906
     * 修改身份验证记录
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function modRecord(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'country_id' => 'required',
            'id_card_no' => 'required',
            'front_pic' => 'required',
            'opposite_pic' => 'required',
        ]);

        $countryId = $request->get('country_id');
        $idCardNo = $request->get('id_card_no');

        if($countryId == 2){
            $reg = '/(^((\s?[A-Za-z])|([A-Za-z]{2}))\d{6}\((([0−9aA])|([0-9aA]))\)$)/';
        }elseif ($countryId == 3){
            $reg = '/^[1|5|7][0-9]{6}\([0-9Aa]\)/';
        }elseif ($countryId == 4){
            $reg = '/^[a-zA-Z][0-9]{9}$/';
        }else{
            $reg = '/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/';
        }
        if(!preg_match($reg,$idCardNo)){
            throw new ParamInvalidException('请输入正确的身份证号码');
        }

        $saveData = [
            'name' => $request->get('name'),
            'country_id' => $request->get('country_id'),
            'id_card_no' => $request->get('id_card_no'),
            'front_pic' => $request->get('front_pic'),
            'opposite_pic' => $request->get('opposite_pic')
        ];
        UserIdentityAuditRecordService::modRecord($saveData, $request->get('current_user'));
        return Result::success('修改成功');
    }

    /**
     * 获取用户验证记录
     * Author:  zwg
     * Date:    180906
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getRecord(Request $request)
    {
        $record = UserIdentityAuditRecordService::getRecordByUserId($request->get('current_user')->id);
        if(empty($record)){
            return Result::success('暂无验证记录');
        }
        $record->country_name = Country::where('id',$record->country_id)->value('name_zh');
        return Result::success($record);
    }
}