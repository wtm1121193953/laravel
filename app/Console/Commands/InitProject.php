<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

function log($message = ''){
    echo $message . "\n";
}

class InitProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init';

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

        // 复制环境变量文件
        if(!file_exists(base_path('.env'))){
            $this->envConfig();
        }else {
            $this->info("环境配置文件已存在, 跳过环境配置");
        }

        $basePath = base_path();
        $this->info("生成应用秘钥");
        echo `php $basePath/artisan key:gen`;
        $this->info("秘钥生成完成");

        // 测试数据库连接是否正确, 以及数据库是否存在
        // 初始化数据库及数据迁移文件
        $this->info("初始化数据库");
        echo `php $basePath/artisan migrate`;
        $this->info("数据库初始化完成");

        $this->info("开始基础数据填充");
        echo `php $basePath/artisan db:seed`;
        $this->info("基础数据填充完成");
        return ;
    }

    /**
     * 初始化环境配置
     */
    private function envConfig()
    {
        // 读取环境变量文件模板
        $envExample = file_get_contents(base_path('.env.example'));
        $envArray = explode("\n", $envExample);
        // 应用名
        // 要修改的项以及对应的描述
        $envDescs = [
            'APP_NAME' => '应用名',
            'APP_URL' => '应用根路径',
//            'DB_CONNECTION' => '数据库类型',
            'DB_HOST' => '数据库主机地址',
            'DB_PORT' => '数据库主机端口',
            'DB_DATABASE' => '数据库名',
            'DB_USERNAME' => '数据库用户名',
            'DB_PASSWORD' => '数据库密码',
            'DB_PREFIX' => '数据库表前缀',
        ];
        foreach($envArray as $index => $env){
            if(empty($env) || strpos($env, '=') === false ){
                continue;
            }
            $env = explode("=", $env);
            $key = trim($env[0]);
            $value = trim($env[1]);
            if(isset($envDescs[$key])){
                $value = $this->ask("{$envDescs[$key]}:", $value);
            }
            $envArray[$index] = $key . '=' . $value;

        }
        $envContent = implode("\n", $envArray);

        $envFile = base_path('.env');
        if(!file_exists($envFile)){
            touch($envFile);
        }
        file_put_contents($envFile, $envContent);
    }
}
