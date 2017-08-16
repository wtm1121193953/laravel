<?php
/**
 * User: Evan Lee
 * Date: 2017/6/28
 * Time: 14:27
 */

namespace App\Exceptions;


use App\ResultCode;

class FileUploadException extends BaseResponseException
{

    public function __construct($message = "文件上传失败")
    {
        parent::__construct($message, ResultCode::UPLOAD_ERROR);
    }
}