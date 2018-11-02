<?php
/**
 *
 */

namespace App\Exports;

use App\Modules\Invite\InviteChannelService;
use App\Support\Utils;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperInviteExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;
    protected $channels;

    public function __construct($query, $oper_id)
    {
        $this->query = $query;
        $this->channels = InviteChannelService::allOperInviteChannel($oper_id);
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
            '注册时间',
            '手机号',
            '微信昵称',
            '渠道',
            '下单次数',
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
            $row->user_created_at,
            Utils::getHalfHideMobile($row->mobile),
            $row->wx_nick_name,
            $this->channels[$row->invite_channel_id],
            ''.$row->order_count
        ];
    }
}