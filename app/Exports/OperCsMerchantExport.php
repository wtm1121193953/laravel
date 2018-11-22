<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 18:40
 */

namespace App\Exports;

use App\Modules\Cs\CsMerchant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 运营中心订单导出
 * Class OperOrderExport
 * @package App\Exports
 */
class OperCsMerchantExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * 定义表头
     * @return array
     */
    public function headings(): array
    {
        return [
            '添加时间',
            '商户ID',
            '商户名称',
            '商户类型',
            '商户招牌名',
            '城市',
            '商户状态',
            '审核状态',
        ];
    }

    /**
     * 定义数据结构
     * @param mixed $data
     *
     * @return array
     */
    public function map($data): array
    {
        return [
            $data->created_at,
            $data->id,
            $data->name,
            '超市商户',
            $data->signboard_name,
            $data->city . ' ' . $data->area,
            CsMerchant::getMerchantStatusText($data->audit_status,$data->status),
            ['待审核', '审核通过', '审核不通过', '重新提交审核'][$data->audit_status],
        ];
    }



}