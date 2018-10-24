<?php
namespace App\Support;
use App\Modules\Log\LogDbService;
use App\Modules\Log\LogReapalPayRequest;
/**
 * 出纸机调用类
 * Class PaperMachine
 * @package App\Support
 */
class PaperMachine{
    protected $appId = '';
    protected $key	 = '';
    protected $userId = '';
    protected $userString = '';

    public function __construct($appId='uY3MiF9cgior',$key='JNrDUihW65SGsSXNGtzBc9eMDUgtnzZl',$userInfo=[]){
        $this->appId = $appId;
        $this->key 	 = $key;
        if(!empty($userInfo)){
            $this->createUserId($userInfo);
        }
    }

    public function curlPost($url,$post=''){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);

        return $data;

    }

    /**
     * 创建sign签名
     * @return string
     */
    protected function createSign(){
        $sign = http_build_query([
            'appid'		=> $this->appId,
            'user_id'	=> $this->userId,
            'key'		=> $this->key
        ]);
        $sign = strtoupper(md5($sign));
        return $sign;

    }

    public function createUserId($userInfo){
        $needColumns = ['nickName','gender'/*,'country','province','city'*/];
        $string = '';
        foreach ($needColumns as $k => $v) {
            if(is_object($userInfo)){
                if($userInfo->$v){
                    $string .= $userInfo->$v;
                }
            }else{
                if($userInfo[$v]){
                    $string .= $userInfo[$v];
                }
            }

        }
        $this->userString = $string;
        $this->userId = strtoupper(md5($string));
        return $this->userId;
    }

    public function send( $url ){
        $postData = [
            'appid'		=> $this->appId,
            'user_id'	=> $this->userId,
            'sign'		=> $this->createSign()
        ];
        $data = $this->curlPost($url, $postData);
        LogDbService::paperMachineRequest(json_encode(['url'=>$url,'postData'=>$postData,'userString'=>$this->userString]),$data,'');
        return $data;
    }
}