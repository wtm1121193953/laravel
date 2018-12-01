<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 12:02
 */
namespace App\Modules\Payment;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Wallet\Wallet;
use Illuminate\Database\Eloquent\Builder;

class PaymentService extends BaseService
{

    public static function getList(array $params=[])
    {

        $query = Payment::query()
            ->when($params['name'],function (Builder $query) use($params) {
                $query->where('name','like',"%{$params['name']}%");
            })
            ->when($params['type'],function (Builder $query) use ($params) {
                $query->where('type',$params['type']);
            })
        ;


        $query->orderBy('id','desc');
        $data = $query->paginate();
        if ($data) {
            $types = Payment::getAllType();
            $data->each(function ($item) use ($types) {

                $item->type_val = $types[$item->type]??'无';


            });
        }
        return $data;
    }


    /**
     * 获取可用支付方式
     * @param int $type 支付方式类型
     * @param int $terminal 终端 1 pc 2 app 3 小程序
     * @return mixed
     */
    public static function getPayment(int $type, int $terminal)
    {
        $terminals = [
            1=>'on_pc',
            2=>'on_app',
            3=>'on_miniprogram'
        ];
        if (empty($terminals[$terminal])) {
            throw new ParamInvalidException('参数错误');
        }

        $payment = Payment::where('status',1)
            ->where('type',$type)
            ->where($terminals[$terminal],'1')
            ->get();

        if (count($payment) !== 1) {
            throw new BaseResponseException('支付方式配置有误');
        }

        return $payment[0];
    }

    /**
     * 根据平台查询数据
     * @param array $whereArr // 待查询字段数组
     * @param $wallet
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getListByPlatForm($whereArr,$wallet)
    {
        $query = Payment::query()
            ->where('status',Payment::STATUS_ON)
        ;
        foreach ($whereArr as $k => $v){
            $query = $query->where($k,$v);
        }
        $list = $query->orderBy('id','acs')->get();
        foreach ($list as $k => $v){
            // ID 4为钱包支付
            if(($v['id']==4) && $wallet['status']==Wallet::STATUS_OFF){
                unset($list[$k]);
                continue;
            }
            $list[$k]['need_password'] = ($v['id']==4) ? true : false;
        }
        $result = array();
        foreach ($list as $k => $v){
            array_push($result,$v);
        }
        return $result;
    }


    public static function getDetailById($id)
    {
        return Payment::where('id',$id)->where('status',Payment::STATUS_ON)->first();
    }
}