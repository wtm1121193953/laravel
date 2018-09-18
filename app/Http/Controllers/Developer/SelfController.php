<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/12
 * Time: 20:05
 */

namespace App\Http\Controllers\Developer;


use App\Exceptions\PasswordErrorException;
use App\Http\Controllers\Controller;
use App\Result;

class SelfController extends Controller
{

    private $users = [
        'dev' => '123456'
    ];

    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $username = request('username');
        $password = request('password');

        if(isset($this->users[$username]) && $this->users[$username] == $password){

            $user = ['username' => $username, 'password' => $password];
            $menus = $this->getMenus();
            session([
                'developer_user' => $user,
            ]);

            return Result::success([
                'user' => $user,
                'menus' => $menus
            ]);
        }else {
            throw new PasswordErrorException();
        }
    }

    public function getRules()
    {
        return Result::success([
            'user' => request()->get('current_user'),
            'menus' => $this->getMenus(),
        ]);
    }

    private function getMenus()
    {
        return [];
    }
}