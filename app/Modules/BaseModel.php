<?php

namespace App\Modules;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 基础模型
 *
 * @mixin Builder
 * @method static static where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static static whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static static select($columns = ['*'])
 * @method static static find($id, $columns = ['*'])
 * @method int increment($column, $amount = 1, array $extra = [])
 */
class BaseModel extends Model
{

}