<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/4/004
 * Time: 17:14
 */

namespace App;


use App\Exceptions\ParamInvalidException;
use Illuminate\Support\Facades\App;
use MongoDB\Client;
use Monolog\Handler\MongoDBHandler;
use Monolog\Logger;

class MongoDBLogger
{
    /**
     * 创建一个 Monolog 实例。
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        if (empty($config['uri'])) {
            throw new ParamInvalidException('确少uri');
        }
        $uri = $config['uri'];
        $uriOptions = $config['uriOptions'] ??  [];
        $driverOptions = $config['driverOptions'] ??  [];
        $client = new Client ($uri, $uriOptions, $driverOptions);
        return new Logger(App::environment(), [
            new MongoDBHandler($client, $config['database'], $this->getCollection($config), $config['level'] ?? 'debug'),
        ]);
    }

    private function getCollection(array $config)
    {

        if (isset($config['daily']) && $config['daily']) {

            return $config['collection'] . date('-Y-m-d');

        } else {
            return $config['collection'];
        }
    }
}