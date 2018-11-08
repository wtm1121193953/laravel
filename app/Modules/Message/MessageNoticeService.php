<?php

namespace App\Modules\Message;

use App\BaseService;
use App\Exceptions\BaseResponseException;

class MessageNoticeService extends BaseService
{
    /**
     * @param $userId
     * @return mixed
     */
    public static function getNoticesByUserId($userId)
    {
        $data = MessageNotice::where('user_id', $userId)
            ->orderBy('is_read', 'asc')
            ->orderBy('id', 'desc')
            ->paginate();
        return $data;

    }

    /**
     * 清除小红点
     * @param $userId
     */
    public static function cleanNeedViewByUserId($userId)
    {
        MessageNotice::where('user_id', $userId)
            ->update(['is_view'=>MessageNotice::IS_VIEW_VIEWED]);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public static function getNeedViewNumByUserId($userId)
    {
        return MessageNotice::where('user_id', $userId)
                ->where('is_view',MessageNotice::IS_VIEW_NORMAL)
                ->count();
    }

    /**
     * @param $userId
     * @param $id
     * @return mixed
     */
    public static function getNoticeDetailByUserIdNId($userId,$id)
    {
        $notice = MessageNotice::where('user_id',$userId)
                            ->where('id',$id)
                            ->first();
        if(!$notice){
            throw new BaseResponseException('找不到该消息');
        }
        if($notice->is_read==MessageNotice::IS_READ_NORMAL){
            // 修改状态为已阅
            $notice->is_read = MessageNotice::IS_READ_READED;
            $notice->save();
        }
        return $notice;
    }

    public static function createByRegister($mobile,$userId)
    {
        $content = "邀请消息：\n手机号码为{$mobile}的好友已注册成功。";
        $messageNotice = new MessageNotice();
        $messageNotice->title = "邀请消息： 手机号码为{$mobile}的好友已注册成功";
        $messageNotice->content = $content;
        $messageNotice->user_id = $userId;
        $messageNotice->is_view = MessageNotice::IS_VIEW_NORMAL;
        $messageNotice->is_read = MessageNotice::IS_READ_NORMAL;
        $messageNotice->save();
        return $messageNotice;
    }
    public static function getRedDotCountsByUserId($userId)
    {
        return MessageNotice::where('user_id',$userId)
            ->where('is_view',MessageNotice::IS_VIEW_NORMAL)
            ->count();
    }

}
