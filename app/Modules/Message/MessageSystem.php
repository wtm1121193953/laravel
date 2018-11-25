<?php

namespace App\Modules\Message;

use Illuminate\Database\Eloquent\Model;

/**
 * 系统消息服务类
 * Class System
 * @package App\Modules\Message
 * @property string title
 * @property string content
 * @property string object_type
 */
class MessageSystem extends Model
{
    protected $table = 'message_system';
    const OB_TYPE_USER = 1;         // 用户
    const OB_TYPE_MERCHANT = 2;     // 商户
    const OB_TYPE_BIZER = 3;        // 业务员
    const OB_TYPE_OPER = 4;         // 运营中心
    const OB_TYPE_CS_MERCHANT = 5;         // 超市商户

}
