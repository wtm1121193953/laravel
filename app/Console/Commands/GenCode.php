<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 22:55
 */

namespace App\Console\Commands;


use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

trait GenCode
{

    /**
     * 获取生成代码需要的属性
     * @param $title string 标题名(中文名)
     * @param $name string 名称
     * @param $pluralName string 复数形式名称
     * @param $module
     * @return array
     */
    private function getVariable($title, $name, $pluralName, $module)
    {
        $studlyModuleName = studly_case($module);
        $studlyName = studly_case($name);
        $listApi = "/{$pluralName}";
        $allListApi = "/{$pluralName}/all";
        $addApi = "/{$name}/add";
        $editApi = "/{$name}/edit";
        $changeStatusApi = "/{$name}/changeStatus";
        $delApi = "/{$name}/del";
        $variable = [
            '{title}' => $title,
            '{name}' => $name,
            '{studlyName}' => $studlyName,
            '{studlyModuleName}' => $studlyModuleName,
            '{listApi}' => $listApi,
            '{allListApi}' => $allListApi,
            '{addApi}' => $addApi,
            '{editApi}' => $editApi,
            '{changeStatusApi}' => $changeStatusApi,
            '{delApi}' => $delApi,
        ];
        return $variable;
    }

    /**
     * 展示生成信息
     * @param $result array
     */
    public function displayGenInfo($result){
        $this->info("文件生成结束");
        $this->info("分页列表接口地址: {$result['{listApi}']}");
        $this->info("全部列表接口地址: {$result['{allListApi}']}");
        $this->info("新增接口地址: {$result['{addApi}']}");
        $this->info("编辑接口地址: {$result['{editApi}']}");
        $this->info("修改状态接口地址: {$result['{changeStatusApi}']}");
        $this->info("删除接口地址: {$result['{delApi}']}");
    }

    /**
     * 生成后台控制器及路由
     * @param $title string 标题名(中文名)
     * @param $name string 名称
     * @param $pluralName string 复数形式名称
     * @param $modelClass string 模型类名
     * @param $force bool 是否强制写入
     * @param string $module
     * @return array
     */
    public function genPhpCode($title, $name, $pluralName, $modelClass, $force = false, $module='admin')
    {
        $variable = $this->getVariable($title, $name, $pluralName, $module);

        $studlyName = $variable['{studlyName}'];
        $variable['{modelClass}'] = $modelClass;
        $modelClassName = substr($modelClass, strrpos($modelClass, '\\') + 1);
        $variable['{modelClassName}'] = $modelClassName;

        // 生成控制器
        $controllerOutputPath = app_path('/Http/Controllers/' . studly_case($module));
        $controllerClassName = "App/Http/Controllers/" . studly_case($module) . "/{$studlyName}Controller";
        if(class_exists($controllerClassName) && !$force){
            $this->throwFileOrDirExistException("控制器类[$controllerClassName]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/php/controller')), $controllerOutputPath, $variable);
        }
        $this->info("控制器生成完成, 控制器类: $controllerClassName");

        // 生成路由文件
        $laravelRouteOutputPath = base_path('routes/api/' . $module);
        $laravelRouteFile = "$laravelRouteOutputPath/$name.php";
        if(file_exists($laravelRouteFile) && !$force){
            $this->throwFileOrDirExistException("laravel路由文件[$laravelRouteFile]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/php/route')), $laravelRouteOutputPath, $variable);
        }
        $this->info("laravel路由文件生成完成, 文件路径: $laravelRouteFile");
        $this->info("    请在[routes/api/$module.php]中加载路由文件: [ Route::group([], base_path('routes/api/$module/{$name}.php')); ]");
        return $variable;
    }

    /**
     * 生成后台vue代码 (vue模板,vue路由及vuex文件)
     * @param $title string 标题名(中文名)
     * @param $name string 名称
     * @param $pluralName string 复数形式名称
     * @param $force bool 是否强制写入
     * @return array
     */
    public function genVueCode($title, $name, $pluralName, $force=false, $module='admin'){

        $variable = $this->getVariable($title, $name, $pluralName, $module);

        // 输出 vue 模板文件
        $templateOutputPath = resource_path($module . '/components/' . $name);
        if(file_exists($templateOutputPath) && !$force){
            $this->throwFileOrDirExistException("vue模板目录[$templateOutputPath]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/vue/template')), $templateOutputPath, $variable);
        }
        $this->info("vue模板生成完成, 模板目录: $templateOutputPath");

        // 输出 vue 路由文件
        $vueRouteOutputPath = resource_path($module . '/routes');
        $routeFile = "$vueRouteOutputPath/$name.js";
        if(file_exists($routeFile) && !$force){
            $this->throwFileOrDirExistException("vue route 文件[$routeFile]");
        }else {
            $this->putStub($this->findStub(resource_path('/stubs/vue/route')), $vueRouteOutputPath, $variable);
        }
        $this->info("vue路由生成完成, 路由文件: $routeFile");
        $this->info("    请在[resources/$module/routes/index.js]中加载路由文件: [ import {$name} from './{$name}' ], 并在路由数组中使用: [ ...{$name}, ]");

        return $variable;
    }

    /**
     * Find stubs.
     *
     * @param string $stubPath
     * @return \Symfony\Component\Finder\Finder
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function findStub($stubPath): Finder
    {
        $finder = new Finder();

        return $finder->files()
            ->in($stubPath)
            ->ignoreVCS(false)
            ->ignoreDotFiles(false);
    }

    /**
     * Put stub.
     *
     * @param \Symfony\Component\Finder\Finder $stubs
     * @param string $outputPath
     * @param array $variable
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function putStub(Finder $stubs, string $outputPath, array $variable = [])
    {
        $this->getOutput()->progressStart($stubs->count());
        foreach ($stubs as $file) {
            $content = $file->getContents();

            $filenameDir = $outputPath.'/'.$file->getRelativePath();
            $filename = $filenameDir.'/'.$file->getBasename();
            if ($file->getExtension() === 'stub') {
                $filename = $filenameDir.'/'.$file->getBasename('.stub');
            }

            foreach ($variable as $key => $value) {
                $content = str_replace($key, $value, $content);
                $filename = str_replace($key, $value, $filename);
                $filenameDir = str_replace($key, $value, $filenameDir);
            }

            $filename = str_replace('\\', '/', $filename);
            $filenameDir = str_replace('\\', '/', $filenameDir);
            if (! file_exists($filenameDir)) {
                mkdir($filenameDir, 0777, true);
            }

            file_put_contents($filename, $content);
            $this->getOutput()->progressAdvance(1);
        }

        $this->getOutput()->progressFinish();
    }

    protected function throwFileOrDirExistException($fileIntro){
        throw new RuntimeException("{$fileIntro}已存在, 停止生成文件, 可使用 --force 选项强制覆盖文件");
    }

}