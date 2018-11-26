<?php

namespace App\Modules\Message;

use App\BaseService;
use App\Modules\Message\MessageSystem;
use App\Exceptions\BaseResponseException;
use Illuminate\Database\Eloquent\Builder;

class MessageSystemService extends BaseService
{
    /**
     * Author:  Jerry
     * Date:    181018
     * @param $postData [title|content|object_type]
     * @return MessageSystem
     */
    public static function createSystem($postData)
    {
        $objectType = [MessageSystem::OB_TYPE_USER, MessageSystem::OB_TYPE_MERCHANT, MessageSystem::OB_TYPE_BIZER, MessageSystem::OB_TYPE_OPER, MessageSystem::OB_TYPE_CS_MERCHANT];
        // 验证参数是否合法
        foreach ($postData['object_type'] as $k=>$v) {
            if (!in_array($v, $objectType)) {
                throw new BaseResponseException('角色类型不合法');
            }
        }
        // 去重
        $postData['object_type'] = array_unique($postData['object_type']);
        $messageSystem = new MessageSystem();
        $messageSystem->title = $postData['title'];
        $messageSystem->content = $postData['content'];
        $messageSystem->object_type = implode(',', $postData['object_type']);
        $messageSystem->save();
        return $messageSystem;
    }

    /**
     * @param $params
     * @return mixed
     */
    public static function getList($params)
    {
        $startTime = $params['start_time'];
        $endTime   = $params['end_time'];
        $content   = $params['content'];
        $objectType= $params['object_type'];
        $data = MessageSystem::when( $startTime, function (Builder $query) use ($startTime) {
                    $query->where('created_at','>', $startTime);
                })
                ->when( $endTime, function (Builder $query) use ($endTime) {
                    $query->where('created_at','<', $endTime);
                })
                ->when( $content, function (Builder $query) use ($content) {
                    $query->where('content','like', "%$content%");
                })
                ->when( $objectType, function (Builder $query) use ($objectType) {
                    $query->where('object_type','like', "%$objectType%");
                })
            ->orderBy('id', 'desc')
            ->paginate();
        return $data;
    }

    public static function getSystemDetailById($id,$user)
    {
        $system = MessageSystem::where('id',$id)->first();
        if(!$system){
            throw new BaseResponseException('找不到该消息');
        }
        // 添加记录已阅状态
//        MessageSystemUserBehaviorRecordService::addRecords($user->id,'is_read',[$id]);
        return $system;
    }

    public static function getRedDotCountsByUserId($userId)
    {
        $record = MessageSystemUserBehaviorRecordService::getRecordByUserId($userId);
        $notInIds = empty($record->is_view) ? [] : json_decode($record->is_view);
        return MessageSystem::where('object_type',MessageSystem::OB_TYPE_USER)
                    ->whereNotIn('id',$notInIds)
                    ->count();
    }
}
