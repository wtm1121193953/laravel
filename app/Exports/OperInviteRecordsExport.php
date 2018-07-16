<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/8
 * Time: 15:25
 */

namespace App\Exports;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 运营中心邀请记录导出
 * Class OperInviteRecordsExport
 * @package App\Jobs
 */
class OperInviteRecordsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            '注册时间',
            '手机号',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->user->id,
            $row->user->created_at,
            $row->user->mobile,
        ];
    }
}