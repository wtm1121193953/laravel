<?php

namespace App\Modules\Message;

use App\BaseService;
//use App\Modules\Message\MessageSystem;
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
        $objectType = [MessageSystem::OB_TYPE_USER, MessageSystem::OB_TYPE_MERCHANT, MessageSystem::OB_TYPE_BIZER, MessageSystem::OB_TYPE_OPER];
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

    public static function getSystemDetailById($id)
    {
        $system = MessageSystem::where('id',$id)->first();
        if(!$system){
            throw new BaseResponseException('找不到该消息');
        }
        return $system;
    }
}
