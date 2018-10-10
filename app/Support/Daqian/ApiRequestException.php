<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/28
 * Time: 20:16
 */

namespace App\Support\Daqian;


class ApiRequestException extends \Exception
{
    protected $content;

    public function __construct(string $message = "", array $content = [])
    {
        $this->content = $content;
        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }
}