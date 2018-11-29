<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:31
 */

namespace App\Support;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Utils
{

    public static function convertListToTree($list, $pid=0, $tier = null, $pidKey = 'pid', $idKey = 'id')
    {
        if(!is_null($tier)) $tier--;
        $tree = [];
        foreach ($list as &$item) {
            if($item->{$pidKey} == $pid){
                if(is_null($tier) || $tier > 0){
                    $sub = self::convertListToTree($list, $item->{$idKey}, $tier, $pidKey, $idKey);
                    if(count($sub) > 0){
                        $item->sub = $sub;
                    }
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    public static function getRequestContext(Request $request)
    {
        $currentUser = $request->get('current_user');
        if($currentUser instanceof Model){
            $currentUser = $currentUser->toArray();
        }

        $data = [
            'ip' => $request->ip(),
            'fullUrl' => $request->fullUrl(),
            'header' => $request->header(),
            'params' => $request->all(),
            'current_user' => $currentUser,
        ];
//        if($request->hasSession()){
//            $data['session'] = $request->session()->all();
//        }
        return $data;
    }

    /**
     * 获取半隐藏的手机号
     * @param $mobile
     * @return string
     */
    public static function getHalfHideMobile($mobile)
    {
        return substr($mobile, 0, 3) . '****' . substr($mobile, -4);
    }

    /**
     * 生成UUID
     * @param string $prefix
     * @return string
     */
    public static function create_uuid($prefix = ""){    //可以指定前缀
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $prefix . $uuid;
    }

    /**
     * 保留n位小数 且 不四舍五入
     * @param $number
     * @param int $decimalQuantity
     * @return float|int
     */
    public static function floorDecimal($number, $decimalQuantity = 2)
    {
        if ($decimalQuantity < 0) $decimalQuantity = 2;
        $pow = pow(10, $decimalQuantity);
        $decimal = floor($number * $pow) / $pow;
        $decimal = number_format($decimal, $decimalQuantity, '.', '');
        return $decimal;
    }

    /**
     * 格式化距离
     * @param $distance
     * @return string
     */
    public static function getFormativeDistance($distance)
    {
        return $distance >= 1000 ? (number_format($distance / 1000, 1) . 'Km') : ($distance . 'm');
    }


    /**
     * aes 加密
     * @param $data
     * @return string
     */
    public static function aesEncrypt($data)
    {
        $key = 'abcdefghijklmnop'; // 16位（也可以不是16位，但当它大于16位时，7.1的openssl函数会截取前16位，有点坑）
        $iv = '1234567890123456'; // 16位
        //$data = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, self::hexToStr($iv));
        $data = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
//        $data = base64_encode($data);
        return $data;
    }

    /**
     * aes 解密
     * @param $data
     * @return string
     */
    public static function aesDecrypt($data)
    {
        $key = 'abcdefghijklmnop'; // 16位（也可以不是16位，但当它大于16位时，7.1的openssl函数会截取前16位，有点坑）
        $iv = '1234567890123456'; // 16位
        //$decrypted = openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, self::hexToStr($iv));
        $decrypted = openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted;
    }

}