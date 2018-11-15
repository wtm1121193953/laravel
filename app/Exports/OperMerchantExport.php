<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 18:40
 */

namespace App\Exports;

use App\Modules\Bizer\Bizer;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Oper\OperBizMember;
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
class OperMerchantExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    protected $collection;
    protected $isPilot;

    public function __construct($collection,$isPilot = '')
    {
        $this->collection = $collection;
        $this->isPilot = $isPilot;
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
            '商户招牌名',
            '行业',
            '城市',
            '签约人',
            '商户状态',
            '审核状态',
            //$this->isPilot ? '' : '结算周期',
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
            $data->signboard_name,
            $this->getCategoryPathName($data->merchant_category_id),
            $data->city . ' ' . $data->area,
            ($data->bizer_id!=0) ? $this->getOperBizersName($data->bizer_id) :$this->getOperBizMemberName($data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id,$data->oper_biz_member_code),
            Merchant::getMerchantStatusText($data->audit_status,$data->status),
            ['待审核', '审核通过', '审核不通过', '重新提交审核'][$data->audit_status],
            //$this->isPilot ? '' : ['', '周结', '半月结', '月结', '半年结', '年结', 'T+1', '未知'][$data->settlement_cycle_type],
        ];
    }


    /**
     * 获取行业名称
     * @param $merchant_category_id
     * @return string
     */
    public function getCategoryPathName($merchant_category_id)
    {
        $categoryPath = MerchantCategoryService::getCategoryPath($merchant_category_id);
        $categoryPathName = '';
        foreach ($categoryPath as $item){
            $categoryPathName = $categoryPathName . $item['name'] . ' ';
        }
        return $categoryPathName;
    }

    /**
     * 获取员工
     * @param $oper_id
     * @param $oper_biz_member_code
     * @return string
     */
    public function getOperBizMemberName($oper_id,$oper_biz_member_code){
        $operBizMember = OperBizMember::where('oper_id', $oper_id)->where('code', $oper_biz_member_code)->first();
        return (empty($operBizMember->name))? '无' : '员工 '.$operBizMember->name.'/'.$operBizMember->mobile;
    }

    public function getOperBizersName($bizerId){
        $bizer =  Bizer::where('id',$bizerId)->first();
        return (empty($bizer->name))? '' : '业务员 '.$bizer->name.'/'.$bizer->mobile;
    }

}