<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/5/005
 * Time: 14:06
 */
namespace App\Modules\MongoDB;

use MongoDB\Collection;
use MongoDB\Driver\Manager;

class Base extends Collection
{

    public static $managerInstance = '';

    protected $database_name = '';
    protected $collection_name = '';
    protected $options = [];

    public function __construct()
    {
        $manager = self::getManagerInstance();
        $this->database_name = 'test';
        parent::__construct($manager, $this->database_name, $this->collection_name, $this->options);

    }

    public static function getManagerInstance()
    {
        $uri = 'mongodb://127.0.0.1/';
        $uriOptions = [];
        $driverOptions = [];

        if (empty(self::$managerInstance)) {
            self::$managerInstance = new Manager($uri, $uriOptions, $driverOptions);
        }

        return self::$managerInstance;
    }




}