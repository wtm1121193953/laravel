<?php
/**
 *
 */

namespace App\Exports;

use App\DataCacheService;
use App\Modules\Cs\CsGood;
use App\Modules\Cs\CsPlatformCategoryService;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CsGoodsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;
    protected $all_cats =[];

    public function __construct($query)
    {
        $this->query = $query;
        $this->all_cats = DataCacheService::getPlatformCats();
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
            '添加时间',
            '商品ID',
            '商品名称',
            '商户名称',
            '一级分类',
            '二级分类',
            '市场价',
            '销售价',
            '库存',
            '销量',
            '简介',
            '状态',
            '审核状态',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->created_at,
            $row->id,
            $row->goods_name,
            $row->cs_merchant->name,
            $this->all_cats[$row->cs_platform_cat_id_level1],
            $this->all_cats[$row->cs_platform_cat_id_level2],
            $row->market_price,
            $row->price,
            $row->stock,
            $row->sale_num,
            $row->summary,
            CsGood::statusName($row->status),
            CsGood::auditStatusName($row->audit_status)
        ];
    }
}