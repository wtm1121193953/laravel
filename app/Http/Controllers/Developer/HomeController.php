<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/12
 * Time: 20:28
 */

namespace App\Http\Controllers\Developer;


use App\Http\Controllers\Controller;
use App\Result;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function index()
    {
//        $connections = DB::getConnections();

        /** @var MySqlConnection $config */
        $config = config('database.mysql');
//        $config->
        $connect = mysqli_connect($config['host'], $config['username'], $config['password'], $config['database']);
        return Result::success([
            'request' => request()->all(),
            'server' => request()->server(),
            'php' => [
                'version' => phpversion(),
            ],
            'mysql' => [
                'version' => mysqli_get_server_info($connect),
            ],
            'apache' => [
                'version' => apache_get_version(),
            ],
        ]);
    }

    public function phpinfo()
    {
        phpinfo();
    }
}