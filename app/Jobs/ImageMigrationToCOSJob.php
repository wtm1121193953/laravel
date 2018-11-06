<?php

namespace App\Jobs;

use App\Http\Controllers\UploadController;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * 用于迁移图片到COS
 * Class ImageMigrationToCOSJob
 * Author:   JerryChan
 * Date:     2018/9/28 14:58
 * @package App\Jobs
 */
class ImageMigrationToCOSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $columns;

    /**
     * ImageMigrationToCOSJob constructor.
     * @param $data
     * @param $columns
     */
    public function __construct( $data, $columns )
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    /**
     * Execute the job.
     * @return void
     */
    /*public function handle()
    {
        $disk = Storage::disk('cosv5');
        $isSave = false;
        foreach ($this->columns as $column => $explode) {
            $tmp = (is_numeric($column)) ? $explode : $column;  // 带分隔符为$column , 不带为$explode
            if(empty($this->data[$tmp])){
                // 如果为空
                continue;
            }
            if (!is_numeric($column)) {
                // 有分隔符
                $arr = explode($explode, $this->data[$tmp]);         // 炸开数组
                if (count($arr) > 1) {
                    // 如果为多张图片
                    $newFileArr = [];
                    foreach ($arr as $k) {
                        $res = $this->upload($k, $disk);
                        if ($res['status']) {
                            $newFileArr[] = $res['url'];
                        } else {
                            Log::error('迁移COS字段上传失败', [
                                'column' => $tmp,
                                'data' => $this->data,
                                'cos_url' => $res['url'],
                                'date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                    if (!empty($newFileArr)) {
                        // 如果有新数据插入
                        $saveFile = implode(',', $newFileArr);
                        if($this->data[$tmp]!=$saveFile){
                            // 避免重复插入
                            $this->data[$tmp] = implode(',', $newFileArr);
                        }
                        $isSave = true;
                    }
                    continue;
                }
            }

            // 如果无分隔符，或者有分隔符只有一条数据，走以下逻辑
            $res = $this->upload($this->data[$tmp], $disk);
            if ($res['status']) {
                // 为cos保存成功，需要入库
                if($this->data[$tmp]!=$res['url']){
                    // 避免重复提交
                    $this->data[$tmp] = $res['url'];
                    $isSave = true;
                }

            } else {
                Log::error('迁移COS字段上传失败', [
                    'column' => $explode,
                    'data' => $this->data,
                    'cos_url' => $res['url'],
                    'date' => date('Y-m-d H:i:s')
                ]);
            }
        }
        if ($isSave && (!$this->data->save())) {
            // 如果入库失败
            Log::error('迁移COS数据保存失败', [
                'column' => $this->columns,
                'data' => $this->data,
                'cos_url' => $res['url'],
                'date' => date('Y-m-d H:i:s')
            ]);
        }
    }*/

    public function handle()
    {
        $disk = Storage::disk('cosv5');
        $isSave = false;
        $explode = ',';
        foreach ($this->columns as $column) {
            $tmp = $column;  // 带分隔符为$column , 不带为$explode
            if(empty($this->data[$tmp])){
                // 如果为空
                continue;
            }
            if (strstr($this->data[$tmp],$explode)) {
                // 有分隔符
                $arr = explode($explode, $this->data[$tmp]);         // 炸开数组
                if (count($arr) > 1) {
                    // 如果为多张图片
                    $newFileArr = [];
                    foreach ($arr as $k) {
                        if(strstr($k,'1257640953')){
                            // 如果已经上传
                            $newFileArr[] = $k;
                            continue;
                        }
                        $res = $this->upload($k, $disk);
                        if ($res['status']) {
                            $newFileArr[] = $res['url'];
                        } else {
                            Log::error('迁移COS字段上传失败', [
                                'column' => $tmp,
                                'data' => $this->data,
                                'cos_url' => $res['url'],
                                'date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                    if (!empty($newFileArr)) {
                        // 如果有新数据插入
                        $saveFile = implode(',', $newFileArr);
                        if($this->data[$tmp]!=$saveFile){
                            // 避免重复插入
                            $this->data[$tmp] = implode(',', $newFileArr);
                        }
                        $isSave = true;
                    }
                    continue;
                }
            }

            // 如果无分隔符，或者有分隔符只有一条数据，走以下逻辑
            if(strstr($this->data[$tmp],'1257640953')){
                // 如果已经上传
                continue;
            }
            $res = $this->upload($this->data[$tmp], $disk);
            if ($res['status']) {
                // 为cos保存成功，需要入库
                if($this->data[$tmp]!=$res['url']){
                    // 避免重复提交
                    $this->data[$tmp] = $res['url'];
                    $isSave = true;
                }

            } else {
                Log::error('迁移COS字段上传失败', [
                    'column' => $explode,
                    'data' => $this->data,
                    'cos_url' => $res['url'],
                    'date' => date('Y-m-d H:i:s')
                ]);
            }
        }
        if ($isSave && (!$this->data->save())) {
            // 如果入库失败
            Log::error('迁移COS数据保存失败', [
                'column' => $this->columns,
                'data' => $this->data,
                'cos_url' => $res['url'],
                'date' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 处理图片上传
     * Author:   JerryChan
     * Date:     2018/9/28 16:34
     * @param string $filename
     * @param        $disk
     * @return array
     */
    private function upload( $filename, $disk )
    {
        $status = false;                // 判断是否上传   false为未上传
        try {
            $file = file_get_contents($filename);       // 旧文件流数据
            $pathInfo = pathinfo($filename);            // 旧文件路径信息
            $newPath = '';
            $newFilename = UploadController::makeName($file, $pathInfo['extension'], $newPath);     // 新文件名字
            if (!$newFilename['status']) {
                if ($disk->put($newPath . '/' . $newFilename['name'], $file)) {
                    $status = true;
                }
            } else {
                // 文件已存在，无需重复上传
                $status = true;
            }

        } catch (\Exception $e) {
            Log::error('COS上传报错：'.implode(',',['message'=>$e->getMessage(),'code'=>$e->getCode(),'line'=>$e->getLine()]));
            return ['status' => $status, 'url' => '图片信息不存在'];
        }
        return ['status' => $status, 'url' => config('cos.cos_url') . '/' . $newPath . $newFilename['name']];
    }

}
