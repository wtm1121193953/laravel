<?php

namespace App\Modules\Message;

use Illuminate\Database\Eloquent\Model;

class MessageSystemUserBehaviorRecordService extends Model
{
    public static function getRecordByUserId($userId)
    {
        return MessageSystemUserBehaviorRecord::firstOrCreate(['user_id' => $userId]);
    }

    public static function addRecords($userId, $type, $ids)
    {
        $record = MessageSystemUserBehaviorRecordService::getRecordByUserId($userId);
        if(is_string($ids)){
            // 兼容小程序逻辑
            if(strstr($ids,',')){
                $ids = explode(',',$ids);
            }else{
                $arr = [];
                array_push($arr,$ids);
                $ids = $arr;
            }
        }
        $needSaveIds = [];
        if (!empty($record->$type)) {
            $needSaveIds = json_decode($record->$type,true);
        }
        foreach ($ids as $k => $v){
            if(!is_numeric($v) || MessageSystem::where('id',$v)->doesntExist()){
                // 非數字不保存,不存在的ID不保存
                continue;
            }
            if(!in_array($v,$needSaveIds)){
                // 保存没有存过的ID
                array_push($needSaveIds,$v);
            }
        }

        $record->$type = json_encode($needSaveIds);
        $record->save();
    }
}
