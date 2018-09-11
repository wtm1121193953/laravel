<?php
/**
 *
 */

namespace App\Exports;

use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecord;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserIdentityExport implements FromQuery, WithHeadings, WithMapping
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
            '提交认证时间',
            '手机号',
            '用户ID',
            '注册时间',
            '姓名',
            '身份证号码',
            '用户状态',
            '认证身份状态'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $row->user->status_val = User::getStatusText($row->user->status);
        $row->status_val = UserIdentityAuditRecord::getStatusText($row->status);
        $rt = [
            $row->created_at,
            $row->user->mobile,
            $row->user_id,
            $row->user->created_at,
            $row->name,
            ' ' . $row->id_card_no,
            $row->user->status_val,
            $row->status_val,
        ];

        return $rt;
    }
}