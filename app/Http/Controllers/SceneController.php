<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/27/027
 * Time: 11:24
 */

namespace App\Http\Controllers;

use App\Modules\Version\VersionService;

class SceneController extends Controller
{

    public function index()
    {

        $scene_id = request('id');

        $data = [];
        $data['ios'] = VersionService::getLastIos();
        $data['android'] = VersionService::getLastAndroid();
        return view('app-download-h5',$data);

    }


}