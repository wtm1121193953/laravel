<?php

namespace App\Modules\Admin;

use App\BaseModel;
use App\Modules\User\GenPassword;

class AdminUser extends BaseModel
{

    use GenPassword;

    //
    protected $hidden = ['password', 'salt'];

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
