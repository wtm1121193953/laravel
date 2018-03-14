<?php

namespace App\Modules\Admin;

use App\BaseModel;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends BaseModel
{
    use HasRoles;

    //
    protected $hidden = ['password', 'salt'];

    /**
     * 用户密码加密
     * @param $password
     * @param $salt
     * @return string
     */
    public static function genPassword($password, $salt){
        return md5(md5($password) . $salt);
    }

    public function isSuper()
    {
        return $this->super == 1;
    }

    /**
     * 判断用户是否具有权限
     * @param $url
     * @return bool
     */
    public function hasPermission($url)
    {
        // 如果是当前用户, 直接从session中获取缓存的权限列表
        /** @var static $currentUser */
        $currentUser = session(config('admin.user_session'));
        if($currentUser->id == $this->id){
            $rules = session(config('admin.user_rule_session'));
        }else {
            $rules = AdminService::getRulesForUser($this);
        }
        return $rules->contains(function($rule, $index) use ($url){
            $ruleUrls = explode(',', $rule['url_all']);
            return in_array($url, $ruleUrls);
        });
    }

}
