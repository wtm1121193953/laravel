<?php

namespace App\Console\Commands;

use App\Support\GeneralCode;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\RuntimeException;

class GenVuePage extends Command
{
    use GeneralCode;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:vue-page {--with-php} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成Vue列表页, 包含添加与编辑弹框, 并包含添加/编辑/修改状态/删除功能';

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
//        $moduleName = $this->ask('模块名(admin):', 'admin');
        $name = $this->ask('功能名:');
        if(!$name){
            throw new RuntimeException('功能名不能为空');
        }
        // laravel 命令行输入中文报错, 还没找到解决办法
//        $title = $this->ask('功能描述[中文名]:');
        if(php_uname('s') == 'Windows NT'){
            $title = 'xxxxxxx';
            $this->info("功能描述[中文名]: 请自行到文件中将[$title]替换为对应的中文名");
        }else {
            $title = $this->ask('功能描述[中文名]:');
        }

        $listApi = $this->ask("列表api地址:", "/{$name}s");
        $addApi = $this->ask("添加api地址:", "/{$name}/add");
        $editApi = $this->ask("编辑api地址:", "/{$name}/edit");
        $changeStatusApi = $this->ask("修改状态api地址:", "/{$name}/changeStatus");
        $delApi = $this->ask("删除api地址:", "/{$name}/del");
        $studlyName = studly_case($name);
        $variable = [
            '{title}' => $title,
            '{name}' => $name,
            '{studlyName}' => $studlyName,
            '{listApi}' => $listApi,
            '{addApi}' => $addApi,
            '{editApi}' => $editApi,
            '{changeStatusApi}' => $changeStatusApi,
            '{delApi}' => $delApi,
        ];

        // 输出 vue 模板文件
        $templateOutputPath = resource_path('admin/components/' . $name);
        if(file_exists($templateOutputPath) && !$this->option('force')){
            $this->throwFileOrDirExistException("vue模板目录[$templateOutputPath]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/vue/template')), $templateOutputPath, $variable);
        }

        // 输出 vue 路由文件
        $vueRouteOutputPath = resource_path('admin/routes');
        if(file_exists($vueRouteOutputPath) && !$this->option('force')){
            $this->throwFileOrDirExistException("vue route 目录[$vueRouteOutputPath]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/vue/route')), $vueRouteOutputPath, $variable);
        }

        if($this->option('with-php')){
            $modelClass = $this->ask('模型类: ', "App\\Modules\\$studlyName\\$studlyName");
            $modelClass = str_replace('/', '\\', $modelClass);
            $variable['{modelClass}'] = $modelClass;
            $modelClassName = substr($modelClass, strrpos($modelClass, '\\') + 1);
            $variable['{modelClassName}'] = $modelClassName;

            // 生成控制器
            $controllerOutputPath = app_path('/Http/Controllers/Admin');
            if(file_exists($controllerOutputPath) && !$this->option('force')){
                $this->throwFileOrDirExistException("控制器[App/Controllers/Admin/{$studlyName}Controller]");
            }else {
                $this->putStub($this->findStub(resource_path('/stubs/php/controller')), $controllerOutputPath, $variable);
            }

            // 生成路由
            $laravelRouteOutputPath = base_path('routes/api/admin');
            if(file_exists($laravelRouteOutputPath) && !$this->option('force')){
                $this->throwFileOrDirExistException("laravel路由文件[$laravelRouteOutputPath/$name.php]");
            }else {
                $this->putStub($this->findStub(resource_path('/stubs/php/route')), $laravelRouteOutputPath, $variable);
            }

        }
        return ;
    }

}
