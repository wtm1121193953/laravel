<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\RuntimeException;

class GenController extends Command
{
    use GenCode;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:controller {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成控制器, 包含添加/编辑/修改状态/删除功能, 并生成对应路由';

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
//        $moduleName = $this->ask('模块名:', 'admin');
        // laravel 命令行输入中文报错, 还没找到解决办法
//        $title = $this->ask('功能描述[中文名]:');
        $module = $this->ask('所属模块(admin|oper|merchant|user)', 'admin');
        if(php_uname('s') == 'Windows NT'){
            $title = 'xxxxxx';
            $this->info("功能描述[中文名]: 请自行到文件中将[$title]替换为对应的中文名");
        }else {
            $title = $this->ask('功能描述[中文名]:');
        }
        $name = $this->ask('功能名:');
        if(!$name){
            throw new RuntimeException('功能名不能为空');
        }
        $pluralName = $this->ask('复数名:', "{$name}s");
        $studlyName = studly_case($name);
        $modelClass = $this->ask('模型类: ', "App\\Modules\\$studlyName\\$studlyName");

        $result = $this->genPhpCode($title, $name, $pluralName, $modelClass, $this->option('force'), $module);

        $this->displayGenInfo($result);
        return ;
    }

}
