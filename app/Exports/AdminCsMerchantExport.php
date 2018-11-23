<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 18:27
 */

namespace App\Exports;

use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Oper\Oper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminCsMerchantExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $id;
    protected $startDate;
    protected $endDate;
    protected $name;
    protected $status;
    protected $settlementCycleType;
    protected $auditStatus;
    protected $operId;
    protected $operName;
    protected $creatorOperId;
    protected $creatorOperName;
    protected $signboardName;
    protected $merchantCategory;

    public function __construct($id = '', $startDate = '',$endDate = '',$signboardName='', $name = '', $status = '', $settlementCycleType = '', $auditStatus = [], $operId = '', $operName = '', $merchantCategory = [])
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->name = $name;
        $this->status = $status;
        $this->settlementCycleType = $settlementCycleType;
        $this->auditStatus = $auditStatus;
        $this->operId = $operId;
        $this->operName = $operName;
        $this->signboardName = $signboardName;
        $this->creatorOperId = '';
        $this->creatorOperName = '';
        $this->merchantCategory = $merchantCategory;
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
        $status = $this->status;
        $settlementCycleType = $this->settlementCycleType;
        $auditStatus = $this->auditStatus;
        $operId = $this->operId;
        $operName = $this->operName;
        $creatorOperId = $this->creatorOperId;
        $creatorOperName = $this->creatorOperName;
        $signboardName = $this->signboardName;
        $merchantCategory = $this->merchantCategory;

        $operIds = null;
        if($operName) {
            $operIds = Oper::where('name', 'like', "%$operName%")
                ->select('id')
                ->get()
                ->pluck('id');
        }

        $createOperIds=null;
        if($creatorOperName){
            $createOperIds = Oper::where('name', 'like', "%$creatorOperName%")
                ->select('id')
                ->get()
                ->pluck('id');
        }
        $query = CsMerchantService::getList([
            'id' => $id,
            'name' => $name,
            'signboardName' => $signboardName,
            'operId' => $operIds ?? $operId,
            'creatorOperId' => $createOperIds ?? $creatorOperId,
            'status' => $status,
            'settlementCycleType' => $settlementCycleType,
            'auditStatus' => $auditStatus,
            'merchantCategory' => $merchantCategory,
            'startCreatedAt' => $startDate,
            'endCreatedAt' => $endDate,
        ], true);

        return $query;
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
            $data->name,
            '超市类',
            $data->signboard_name,
            $data->operId = $data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id,
            Oper::where('id', $data->oper_id > 0 ? $data->oper_id : $data->audit_oper_id)->value('name'),
            $data->city . ' ' . $data->area,
            CsMerchant::getMerchantStatusText($data->audit_status,$data->status),
            ['待审核', '审核通过', '审核不通过', '重新提交审核'][$data->audit_status],
            ['', '周结', '半月结', 'T+1(自动)', '半年结', '年结', 'T+1(人工)', '未知'][$data->settlement_cycle_type],
        ];
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
            '商户名称',
            '商户类型',
            '商户招牌名',
            '激活运营中心ID',
            '激活运营中心名称',
            '城市',
            '商户状态',
            '审核状态',
            '结算周期',
        ];
    }
}