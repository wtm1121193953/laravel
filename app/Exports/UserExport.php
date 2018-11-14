<?php
/**
 *
 */

namespace App\Exports;

use App\Modules\Invite\InviteUserService;
use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecord;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromQuery, WithHeadings, WithMapping
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
            '手机号',
            '用户ID',
            '注册时间',
            '会员名称',
            '分享人',
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

        $row->stauts_val = User::getStatusText($row->status);
        if (!empty($row->identityAuditRecord->status)) {
            $row->identity_status_text = UserIdentityAuditRecord::getStatusText($row->identityAuditRecord->status);
        } else {
            $row->identity_status_text = '未提交';
        }

        $parentName = InviteUserService::getParentName($row->id);
        if($parentName){
            $row->isBind = 1;
            $row->parent = $parentName;
        }else {
            $row->parent = '未绑定';
            $row->isBind = 0;
        }

        $rt = [
            $row->mobile,
            $row->id,
            $row->created_at,
            $row->name,
            $row->parent,
            $row->stauts_val,
            $row->identity_status_text
        ];

        return $rt;
    }
}