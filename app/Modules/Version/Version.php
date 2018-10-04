<?php

namespace App\Modules\Version;

use App\BaseModel;

/**
 * @property string app_name
 * @property string app_tag
 * @property string app_num
 * @property int version_num
 * @property string version_explain
 * @property string package_url
 * @property int status
 * @property int force_update
 * @property int app_type
 * @property float app_size
 */
class Version extends BaseModel
{

    const APP_TYPE_IOS = 2;
    const APP_TYPE_ANDROID = 1;

    const STATUS_UNPUBLISH = 1; // 暂未发布
    const STATUS_PUBLISHED = 2; // 已发布
}
