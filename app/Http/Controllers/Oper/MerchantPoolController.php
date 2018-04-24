<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 14:34
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Result;

/**
 * 商户池控制器, 用户获取未签订合同的商户
 * Class MerchantPoolController
 * @package App\Http\Controllers\Oper
 */
class MerchantPoolController extends Controller
{

    public function getList()
    {
        $data = Merchant::where('contract_status', Merchant::CONTRACT_STATUS_NO)
            ->orderBy('id', 'desc')
            ->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 添加商户池中的商户信息, 即商户的contract_status为2 并且该商户没有所属运营中心
     */
    public function add()
    {
        // todo
    }

    /**
     * 修改商户池中的商户信息
     */
    public function edit()
    {
        // todo
    }
}