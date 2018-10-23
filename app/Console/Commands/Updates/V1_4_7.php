<?php

namespace App\Console\Commands\Updates;

use App\Modules\Oper\OperBizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V1_4_7 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.7';

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
        $sql = "ALTER TABLE `admin_auth_rules`
	CHANGE COLUMN `url_all` `url_all` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '权限全部菜单地址, 使用逗号分隔' COLLATE 'utf8mb4_unicode_ci' AFTER `url`";

        DB::statement($sql);


        OperBizer::chunk(1000, function ($operBizers) {
            foreach ($operBizers as $operBizer) {
                $operBizer->divide = number_format(20, 2);
                $operBizer->save();
            }
        });


        $this->info('执行成功');
    }
}
