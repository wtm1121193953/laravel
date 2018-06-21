<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 18:27
 */

namespace App\Modules\Merchant;


use App\Modules\Oper\Oper;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MerchantExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $id;
    protected $startDate;
    protected $endDate;

    public function __construct($id = '', $startDate = '', $endDate = '')
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * 在 MerchantExport 类中,添加 FromQuery 关系, 并且添加一个查询, 并且确保不要使用 ->get() 来获取到数据!
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $id = $this->id;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $data = Merchant::query()
            ->where('audit_oper_id', '>', 0)
            ->when($id, function (Builder $query) use ($id){
                $query->where('id', $id);
            })
            ->when($startDate, function (Builder $query) use ($startDate){
                $query->where('created_at', '>=', $startDate.' 00:00:00');
            })
            ->when($endDate, function (Builder $query) use ($endDate){
                $query->where('created_at', '<=', $endDate.' 23:59:59');
            })
            ->orderByDesc('id');

        return $data;
    }

    /**
     * 遍历行
     * @param mixed $data
     * @return array
     */
    public function map($data): array
    {
        return [
            $data->created_at,
            $data->id,
            Oper::where('id', $data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id)->value('name'),
            Oper::where('id', $data->creator_oper_id)->value('name'),
            $data->name,
            $this->getCategoryPathName($data->merchant_category_id),
            $data->city . ' ' . $data->area,
            ['待审核', '审核通过', '审核不通过', '待审核(重新提交)'][$data->audit_status],
        ];
    }

    /**
     * 获取行业名称
     * @param $merchant_category_id
     * @return string
     */
    public function getCategoryPathName($merchant_category_id)
    {
        $categoryPath = MerchantCategory::getCategoryPath($merchant_category_id);
        $categoryPathName = '';
        foreach ($categoryPath as $item){
            $categoryPathName = $categoryPathName . $item['name'] . ' ';
        }
        return $categoryPathName;
    }

    /**
     * 添加表头
     * @return array
     */
    public function headings(): array
    {
        return [
            '添加时间',
            '商户ID',
            '激活运营中心',
            '录入运营中心',
            '商户名称',
            '行业',
            '城市',
            '审核状态',
        ];
    }
}