<?php

namespace App\Console\Commands;

use App\Modules\Area\Area;
use App\Support\Amap;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 同步行政区划信息命令
 * Class AreaSync
 * @package App\Console\Commands
 */
class AreaSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'area:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        /*$url = 'http://apis.map.qq.com/ws/district/v1/list?key=NBXBZ-52DR5-IM6IK-QKP45-CDNUJ-ACFVU';
        $client = new Client();
        $response = $client->get($url);
        $response = $response->getBody()->getContents();
        $response = json_decode($response, 1);
        $result = $response['result'];
        $provinces = $result[0];
        $cities = $result[1];
        $areas = $result[2];
        foreach ($provinces as $item) {
            $area = new Area();
            $area->area_id = substr($item['id'], 0, 2);
            $area->name = $item['name'];
            $area->path = 1;
            $area->area_code;
        }*/
        DB::table('areas')->truncate();
        $map = new Amap();
        $result = $map->getDistrict();
        $provinces = $result[0]['districts'];
        foreach ($provinces as $province) {
            $provinceId = substr($province['adcode'], 0, 2);
            $model = new Area();
            $model->area_id = $provinceId;
            $model->name = $province['name'];
            $model->path = 1;
            $model->area_code = empty($province['citycode']) ? '' : $province['citycode'];
            $model->spell = strtoupper(pinyin_permalink($model->name, ''));
            $model->letter = strtoupper(pinyin_abbr($model->name));
            $model->first_letter = substr($model->letter, 0, 1);
            $model->save();

            $cities = $province['districts'];
            foreach ($cities as $city){
                $model = new Area();
                $model->area_id = $city['adcode'];
                $model->name = $city['name'];
                $model->path = 2;
                $model->area_code = empty($city['citycode']) ? '' : $city['citycode'];
                $model->spell = strtoupper(pinyin_permalink($model->name, ''));
                $model->letter = strtoupper(pinyin_abbr($model->name));
                $model->first_letter = substr($model->letter, 0, 1);
                $model->parent_id = $provinceId;
                $model->save();

                $areas = $city['districts'];
                foreach ($areas as $area) {
                    $model = new Area();
                    $model->area_id = $area['adcode'];
                    $model->name = $area['name'];
                    $model->path = 3;
                    $model->area_code = empty($area['citycode']) ? '' : $area['citycode'];
                    $model->spell = strtoupper(pinyin_permalink($model->name, ''));
                    $model->letter = strtoupper(pinyin_abbr($model->name));
                    $model->first_letter = substr($model->letter, 0, 1);
                    $model->parent_id = $city['adcode'];
                    $model->save();
                }
            }
        }
        return ;
    }
}
