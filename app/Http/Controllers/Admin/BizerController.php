<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\OperBizerService;
use App\Result;

class BizerController extends Controller
{
    /**
     * 获取业务员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $mobile = request('mobile', '');
        $id = request('id', 0);
        $name = request('name', '');
        $identityName = request('identityName', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', 0);
        $identityStatus = request('identityStatus', 0);
        $identityStartDate = request('identityStartDate', '');
        $identityEndDate = request('identityEndDate', '');
        $pageSize = request('pageSize', 15);

        $params = compact('mobile', 'id', 'name', 'identityName', 'startDate', 'endDate', 'status', 'identityStatus', 'identityStartDate', 'identityEndDate');
        $data = BizerService::getBizerList($params, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取运营中心全部的业务员列表
     */
    public function getOperAllEnableBizers()
    {
        $this->validate(request(), [
            'operId' => 'required|integer|min:1'
        ]);
        $operId = request('operId');

        $list = OperBizerService::getAllbizer(['oper_ids' => $operId, 'status' => OperBizer::STATUS_SIGNED]);
        return Result::success(['list' => $list]);
    }

    /**
     * 获取业务员详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $bizerId = request('id');
        $bizer = BizerService::getBizerDetail($bizerId);

        return Result::success($bizer);
    }

    /**
     * 更改业务员的状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $bizerId = request('id');
        $bizer = BizerService::changeStatus($bizerId);

        return Result::success($bizer);
    }

    /**
     * 业务员身份信息审核
     * @return \Illuminate\Http\JsonResponse
     */
    public function bizerIdentityAudit()
    {
        $this->validate(request(), [
            'ids' => 'required',
            'status' => 'required',
        ]);
        $ids = request('ids');
        $status = request('status');
        $reason = request('reason', '');
        $user = request()->get('current_user');

        if (is_array($ids)) {
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    BizerService::identityAudit($id, $status, $reason, $user);
                }
            }
        } else {
            BizerService::identityAudit($ids, $status, $reason, $user);
        }

        return Result::success();
    }

    /**
     * 业务员列表导出
     */
    public function exportExcel()
    {
        $mobile = request('mobile', '');
        $id = request('id', 0);
        $name = request('name', '');
        $bizerStartDate = request('startDate', '');
        $bizerEndDate = request('endDate', '');
        $status = request('status', 0);
        $identityStatus = request('identityStatus', 0);
        $identityStartDate = request('identityStartDate', '');
        $identityEndDate = request('identityEndDate', '');
        $pageSize = request('pageSize', 15);

        $params = compact('mobile', 'id', 'name', 'bizerEndDate', 'bizerStartDate', 'status', 'identityStatus', 'identityStartDate', 'identityEndDate');
        $query = BizerService::getBizerList($params, $pageSize, true);
        $data = $query->get()->toArray();

        $fileName = '业务员列表';
        header('Content-Type: application/vnd.ms-execl');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');

        $fp = fopen('php://output', 'a');
        $title = ['提交认证时间', '手机号码', '业务员ID', '注册时间', '昵称', '姓名', '身份证号码', '用户状态', '身份认证状态'];
        foreach ($title as $key => $value) {
            $title[$key] = iconv('UTF-8', 'GBK', $value);
        }
        fputcsv($fp, $title);

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $item = [];
                $item['identity_created_at'] = isset($value['bizer_identity_audit_record']) ? iconv('UTF-8', 'GBK', $value['bizer_identity_audit_record']['created_at']) : '';
                $item['mobile'] = iconv('UTF-8', 'GBK', $value['mobile']);
                $item['id'] = iconv('UTF-8', 'GBK',$value['id']);
                $item['created_at'] = iconv('UTF-8', 'GBK',$value['created_at']);
                $item['name'] = iconv('UTF-8', 'GBK',$value['name']);
                $item['identity_name'] = isset($value['bizer_identity_audit_record']) ? iconv('UTF-8', 'GBK', $value['bizer_identity_audit_record']['name']) : '';
                $idCardNo = '';
                if (isset($value['bizer_identity_audit_record'])) {
                    $arr = str_split($value['bizer_identity_audit_record']['id_card_no']);
                    $idCardNo = implode("\t", $arr);
                }
                $item['id_card_no'] = $idCardNo;
                $item['status'] = iconv('UTF-8', 'GBK',['', '正常', '禁用'][$value['status']]);
                $item['identity_status'] = isset($value['bizer_identity_audit_record']) ? iconv('UTF-8', 'GBK',['', '待审核', '审核通过', '审核不通过', '未提交'][$value['bizer_identity_audit_record']['status']]) : '';
                fputcsv($fp, $item);
            }
            ob_flush();
            flush();
        }
    }

    /**
     * 获取运营中心签约的业务员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOperBizerList() {
        $this->validate(request(), [
            'operId' => 'required'
        ]);
        $bizerMobile = request('mobile', '');
        $bizerName = request('name', '');
        $bizerIds = [];
        if ($bizerMobile || $bizerName) {
            $param = [
                'mobile' => $bizerMobile,
                'name' => $bizerName,
                ];
            $bizerIds = BizerService::getBizerList($param, 15, true)->get()->pluck('id');
        }

        $where =[
            "oper_ids" => request('operId'),//登录所属运营中心ID
            "status" => OperBizer::STATUS_SIGNED,//查询业务员的状态
            "bizer_id" => $bizerIds,
        ];

        $data = OperBizerService::getList($where);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 设置分成比例
     * @return \Illuminate\Http\JsonResponse
     */
    public function setOperBizerDivide()
    {
        $this->validate(request(), [
            'operId' => 'required|integer|min:1',
            'bizerId' => 'required|integer|min:1',
            'divide' => 'required|min:0|max:100',
        ]);
        $operId = request('operId');
        $bizerId = request('bizerId');
        $divide = request('divide');

        $operBizer = OperBizerService::updateOperBizerDivide($operId, $bizerId, $divide);

        return Result::success($operBizer);
    }
}