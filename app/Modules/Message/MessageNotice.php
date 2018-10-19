<?php

namespace App\Modules\Message;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * Class Notice
 * @package App\Modules\Message
 * @property int user_id
 * @property string title
 * @property text content
 * @property int is_read
 * @property int is_view
 *
 */
class MessageNotice extends Model
{
    protected $table = 'message_notice';
    const IS_READ_NORMAL = 1;
    const IS_READ_READED = 2;
    const IS_VIEW_NORMAL = 1;
    const IS_VIEW_VIEWED = 2;
}
