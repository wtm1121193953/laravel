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
    protected $name;
    protected $auditStatus;
    protected $operId;
    protected $operName;
    protected $creatorOperId;
    protected $creatorOperName;
    protected $signBoardName;

    public function __construct($id = '', $startDate = '',$endDate = '',$signBoardName='', $name = '', $auditStatus = [], $operId = '', $operName = '', $creatorOperId = '', $creatorOperName = '')
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->name = $name;
        $this->auditStatus = $auditStatus;
        $this->operId = $operId;
        $this->operName = $operName;
        $this->signBoardName = $signBoardName;
        $this->creatorOperId = $creatorOperId;
        $this->creatorOperName = $creatorOperName;
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
        $name = $this->name;
        $auditStatus = $this->auditStatus;
        if(empty($auditStatus)){
            $auditStatus=["0","1","2","3"];
        }
        $operId = $this->operId;
        $operName = $this->operName;
        $creatorOperId = $this->creatorOperId;
        $creatorOperName = $this->creatorOperName;
        $signBoardName = $this->signBoardName;

        $operIds = [];
        if($operName) {
            $result = Oper::where('name', 'like', "%$operName%")->get();
            if (!$result->isEmpty()){
                foreach ($result as $k => $v) {
                    $operIds[$k] = $v->id;
                }
            }
        }

        $createOperIds=[];
        if($creatorOperName){
            $createResult = Oper::where('name', 'like', "%$creatorOperName%")->get();
            if(!$createResult->isEmpty()){
                foreach ($createResult as $k=>$v){
                    $createOperIds[$k]=$v->id;
                }
            }
        }

        if (($operName && empty($operIds)) || ($creatorOperName && empty($createOperIds))){
            $data = collect();
        }else {
            $data = Merchant::query()
                ->where('audit_oper_id', '>', 0)
                ->when($id, function (Builder $query) use ($id) {
                    $query->where('id', $id);
                })
                ->when($creatorOperId, function (Builder $query) use ($creatorOperId) {
                    $query->where('creator_oper_id', $creatorOperId);
                })
                ->when($operId, function (Builder $query) use ($operId) {
                    if ($operId > 0) {
                        $query->where('oper_id', $operId);
                    } else {
                        $query->where('audit_oper_id', $operId);
                    }
                })
                ->when(!empty($operIds), function (Builder $query) use ($operIds) {
                    $query->whereIn('oper_id', $operIds);
                })
                ->when(!empty($createOperIds), function (Builder $query) use ($createOperIds) {
                    $query->whereIn('creator_oper_id', $createOperIds);
                })
                ->when($startDate, function (Builder $query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate . ' 00:00:00');
                })
                ->when($endDate, function (Builder $query) use ($endDate) {
                    $query->where('created_at', '<=', $endDate . ' 23:59:59');
                })
                ->when(!empty($auditStatus) && isset($auditStatus), function (Builder $query) use ($auditStatus) {
                    $query->whereIn('audit_status', $auditStatus);
                })
                ->when($name, function (Builder $query) use ($name) {
                    $query->where('name', 'like', "%$name%");
                })
                ->when($signBoardName, function (Builder $query) use ($signBoardName) {
                    $query->where('signboard_name', 'like', "%$signBoardName%");
                })
                ->orderByDesc('id');
        }

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
            $data->operId = $data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id,
            Oper::where('id', $data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id)->value('name'),
            $data->creator_oper_id,
            Oper::where('id', $data->creator_oper_id)->value('name'),
            $data->name,
            $data->signboard_name,
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
            '激活运营中心ID',
            '激活运营中心名称',
            '录入运营中心ID',
            '录入运营中心名称',
            '商户名称',
            '商户招牌名',
            '行业',
            '城市',
            '审核状态',
        ];
    }
}