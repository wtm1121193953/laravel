<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Jobs\GoodsCreated;
use App\Modules\Goods\CategoryService;
use App\Modules\Goods\Goods;
use App\Modules\Goods\GoodsService;
use App\Modules\Goods\GoodsSpec;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class GoodsController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $data = Goods::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->select('id', 'name', 'supplier_id', 'category_id', 'brand', 'purchase_price', 'origin_price', 'discount_price', 'spec_type', 'spec_name_1', 'spec_name_2', 'default_spec_id', 'category_id_1', 'category_id_2', 'category_id_3', 'category_id_4', 'default_image', 'small_images', 'status', 'tags', 'created_at', 'updated_at')
            ->orderBy('id', 'desc')
            ->paginate();
        $data->map(function($item){
            return GoodsService::getDetail($item);
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 商品详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $goods = GoodsService::getDetail(request('id'));;
        return Result::success($goods);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'supplier_id' => 'required|integer',
            'category_id' => 'required|integer',
            'purchase_price' => 'required|numeric|min:0',
            'origin_price' => 'required|numeric|min:0',
        ]);
        $goods = new Goods();
        $goods->name = request('name');
        $goods->supplier_id = request('supplier_id');
        $goods->category_id = request('category_id');
        $goods->brand = request('brand', '');
        $goods->purchase_price = request('purchase_price');
        $goods->origin_price = request('origin_price');
        $goods->discount_price = request('discount_price', request('origin_price'));

        /*   获取规格数据   */
        $specData = $this->_getSpecDataFromRequest();

        $goods->spec_type = $specData['spec_type'];
        $goods->spec_name_1 = $specData['spec_name_1'];
        $goods->spec_name_2 = $specData['spec_name_2'];

        // 查找分类的路径
        $categoryPath = CategoryService::getCatePath(request('category_id'));
        foreach ($categoryPath as $index => $category) {
            $goods->{'category_id_' . ($index + 1)} = $category->id;
        }

        $goods->default_image = request('default_image', '');
        $goods->ext_attr = request('ext_attr', '');
        $goods->small_images = request('small_images', '');
        $goods->detail = request('detail', '');
        $goods->status = request('status', 1);
        $goods->tags = request('tags', '');
        $goods->save();

        // 保存规格数据
        $defaultSpec = null;
        foreach ($specData['specs'] as $index => $item) {
            $spec = new GoodsSpec();
            $spec->goods_id = $goods->id;
            $spec->spec_1 = $item['spec_1'];
            $spec->spec_2 = $item['spec_2'];
            $spec->purchase_price = $item['purchase_price'];
            $spec->origin_price = $item['origin_price'];
            $spec->discount_price = $item['discount_price'];
            $spec->stock = $item['stock'];
            $spec->sku = uniqid('G');
            $spec->save();
            if($index == 0){
                $defaultSpec = $spec;
            }
        }

        // 更新默认商品规格
        $goods['default_spec_id'] = $defaultSpec->id;
        $goods->save();

        GoodsCreated::dispatch($goods);

        return Result::success(GoodsService::getDetail($goods));
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'supplier_id' => 'required|integer',
            'category_id' => 'required|integer',
            'origin_price' => 'required|numeric|min:0',
        ]);
        $goods = Goods::findOrFail(request('id'));
        $goods->name = request('name');
        $goods->supplier_id = request('supplier_id');
        $goods->category_id = request('category_id');
        $goods->pict_url = request('pict_url', '');
        $goods->detail = request('detail', '');
        $goods->small_images = request('small_images', '');
        $goods->origin_price = request('origin_price');
        $goods->discount_price = request('discount_price', request('origin_price'));

        $goods->status = request('status', 1);

        $goods->save();

        return Result::success(GoodsService::getDetail($goods));
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $goods = Goods::findOrFail(request('id'));
        $goods->status = request('status');

        $goods->save();

        return Result::success(GoodsService::getDetail($goods));
    }

    /**
     * 修改库存
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeStock()
    {
        $this->validate(request(), [
            'specId' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0'
        ]);
        $stock = request('stock', 0);
        $spec = GoodsSpec::findOrFail(request('specId'));
        $spec->stock = $stock;
        $spec->save();

        return Result::success(GoodsService::getDetail($spec->goods_id));
    }

    /**
     * 删除商品
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $item = Goods::findOrFail(request('id'));
        $item->delete();
        // 删除商品时保留商品的规格信息
        return Result::success();
    }

    private function _getSpecDataFromRequest()
    {
        $useSpec = request('useSpec', false);
        $spec_name_1 = request('spec_name_1', '');
        $spec_name_2 = request('spec_name_2', '');
        if(!$useSpec){
            $spec_type = 0;
        }else {
            if($spec_name_1 && $spec_name_2){
                $spec_type = 2;
            }else if($spec_name_1 || $spec_name_2) {
                $spec_type = 1;
            }
        }

        if($spec_type == 0){
            // 没有规格
            $spec = [
                'spec_1' => '',
                'spec_2' => '',
                'purchase_price' => request('purchase_price', 0),
                'origin_price' => request('origin_price'),
                'discount_price' => request('discount_price', request('origin_price')),
                'stock' => request('stock'),
            ];
            $list = [$spec];
        }else {
            // 开启规格信息
            $specs = request('specs', []);
            if(empty($specs) || sizeof($specs) <= 0){
                throw new ParamInvalidException('开启规格信息时规格不能为空');
            }
            $list = [];

            foreach ($specs as $spec) {
                if($spec_type == 1){
                    // 一个规格
                    $spec_1 = $spec_name_1 ? $spec['spec_1'] : $spec['spec_2'];
                    $spec_2 = '';
                }else {
                    // 两个规格
                    $spec_1 = $spec['spec_1'];
                    $spec_2 = $spec['spec_2'];
                }
                $list[] = [
                    'spec_1' => $spec_1,
                    'spec_2' => $spec_2,
                    'purchase_price' => $spec['purchase_price'],
                    'origin_price' => $spec['origin_price'],
                    'discount_price' => $spec['discount_price'] ?? $spec['origin_price'],
                    'stock' => $spec['stock'],
                ];
            }

            // 一个规格
            if($spec_type == 1){
                $spec_name_1 = $spec_name_1 ?? $spec_name_2;
                $spec_name_2 = '';
            }
        }
        return [
            'spec_name_1' => $spec_name_1,
            'spec_name_2' => $spec_name_2,
            'spec_type' => $spec_type,
            'specs' => $list,
        ];
    }

}