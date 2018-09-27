<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/27/027
 * Time: 11:24
 */

namespace App\Http\Controllers;

class SceneController extends Controller
{

    public function index()
    {

        $scene_id = request('id');
        echo $scene_id;

    }
}