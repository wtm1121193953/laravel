<?php

namespace App;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 基础模型
 *
 * @mixin Builder
 * @method static static select($columns = ['*'])
 * @method static static find($id, $columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static when($value, $callback, $default = null)
 * @method static static where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static static whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static static orderBy($column, $direction = 'asc')
 * @method static LengthAwarePaginator paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
 * @method int increment($column, $amount = 1, array $extra = [])
 */
class BaseModel extends Model
{

}