<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/27/027
 * Time: 11:24
 */

namespace App\Http\Controllers;

use App\Modules\Version\Version;
use App\Modules\Version\VersionService;

class SceneController extends Controller
{

    public function index()
    {

        $scene_id = request('id');

        $data = [];
        $data['ios'] = VersionService::getLastVersionByType(Version::APP_TYPE_IOS);
        $data['android'] = VersionService::getLastVersionByType(Version::APP_TYPE_ANDROID);
        if(!empty($data['ios'])){
            $data['ios']->package_url = "https://itunes.apple.com/cn/app/id1438505884?mt=8";
        }
        return view('app-download-h5',$data);

    }


}