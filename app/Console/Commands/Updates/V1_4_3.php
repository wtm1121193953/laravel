<?php

namespace App\Console\Commands\Updates;

use App\Modules\Admin\AdminAuthRule;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\Order;
use App\Modules\Wallet\Bank;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBalanceUnfreezeRecord;
use App\Modules\Wallet\WalletBatch;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletConsumeQuotaUnfreezeRecord;
use App\Modules\Wallet\WalletWithdraw;
use Illuminate\Console\Command;

class V1_4_3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.3';

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
     * @throws \Exception
     */
    public function handle()
    {
        // 1. 清楚结算出的分润
        // 代码全部注释掉, 以免后面误操作
//        Wallet::where('id', '>', 0)->delete();
//        FeeSplittingRecord::where('id', '>', 0)->delete();
//        WalletBalanceUnfreezeRecord::where('id', '>', 0)->delete();
//        WalletBatch::where('id', '>', 0)->delete();
//        WalletBill::where('id', '>', 0)->delete();
//        WalletConsumeQuotaRecord::where('id', '>', 0)->delete();
//        WalletConsumeQuotaUnfreezeRecord::where('id', '>', 0)->delete();
//        WalletWithdraw::where('id', '>', 0)->delete();
//        Order::where('id', '>', 0)->update(['splitting_status' => 1, 'splitting_time' => null]);
//
//        // 删除错误的权限
//        AdminAuthRule::where('pid', 35)->delete();

        $this->info("\n初始化银行列表 Start");
        $banks = [
            '中国工商银行',
            '中国农业银行',
            '中国银行',
            '中国建设银行',
            '交通银行',
            '中信银行',
            '中国光大银行',
            '华夏银行',
            '中国民生银行',
            '广发银行',
            '深圳发展银行',
            '招商银行',
            '兴业银行',
            '上海浦东发展银行',
            '恒丰银行',
            '浙商银行',
            '渤海银行',
            '中国邮政储蓄银行',
            '北京银行',
            '北京农商银行',
            '天津银行',
            '天津农村商业银行',
            '天津滨海农村商业银行',
            '河北银行',
            '邢台银行',
            '唐山市商业银行',
            '秦皇岛市商业银行',
            '沧州银行',
            '承德银行',
            '邯郸银行',
            '保定市商业银行',
            '廊坊银行',
            '张家口市商业银行',
            '衡水市商业银行',
            '沧州融信农村商业银行',
            '河北文山农村商业银行',
            '包商银行',
            '内蒙古银行',
            '乌海银行',
            '鄂尔多斯银行',
            '鄂尔多斯东胜农村商业银行',
            '阿拉善农村商业银行',
            '巴彦淖尔河套农村商业银行',
            '晋商银行',
            '阳泉市商业银行',
            '长治市商业银行',
            '晋城银行',
            '晋中市商业银行',
            '大同市商业银行',
            '吉林银行',
            '盛京银行',
            '长春农村商业银行',
            '吉林九台农村商业银行',
            '延边农村商业银行',
            '锦州银行',
            '葫芦岛市商业银行',
            '大连银行',
            '鞍山银行',
            '抚顺银行',
            '丹东银行',
            '营口银行',
            '盘锦市商业银行',
            '阜新银行',
            '辽阳银行',
            '铁岭市商业银行',
            '朝阳银行',
            '葫芦岛连山农村商业银行',
            '沈阳农村商业银行',
            '大连农村商业银行',
            '哈尔滨银行',
            '龙江银行',
            '黑龙江东宁农村商业银行',
            '上海银行',
            '上海农村商业银行',
            '南京银行',
            '江苏银行',
            '江苏长江商业银行',
            '苏州银行',
            '无锡农村商业银行',
            '江苏江阴农村商业银行',
            '常熟农村商业银行',
            '江苏吴江农村商业银行',
            '江苏太仓农村商业银行',
            '江苏张家港农村商业银行',
            '江苏射阳农村商业银行',
            '江苏靖江农村商业银行',
            '江苏江南农村商业银行',
            '江苏紫金农村商业银行',
            '江苏江都农村商业银行',
            '泰州农村商业银行',
            '江苏高邮农村商业银行 ',
            '江苏阜宁农村商业银行',
            '宿迁民丰农村商业银行',
            '南通农村商业银行',
            '常州武进农村商业银行',
            '江苏宜兴农村商业',
            '江苏昆山农村商业银行',
            '江苏建湖农村商业银行',
            '江苏丹阳农村商业银行',
            '江苏邳州农村商业银行',
            '江苏泗阳农村商业银行',
            '江苏姜堰农村商业银行',
            '江苏赣榆农村商业银行',
            '江苏仪征农村商业银行',
            '江苏溧水农村商业银行',
            '杭州银行',
            '宁波银行',
            '温州银行',
            '嘉兴银行',
            '湖州银行',
            '绍兴银行',
            '金华银行',
            '台州银行',
            '浙江泰隆商业银行',
            '浙江民泰商业银行',
            '浙江稠州商业银行',
            '杭州联合农村商业银行',
            '浙江南浔农村商业银行',
            '浙江绍兴瑞丰农村商业银行',
            '福建海峡银行',
            '厦门银行',
            '泉州银行',
            '福建上杭农村商业银行',
            '泉州农村商业银行',
            '南平农村商业银行',
            '漳州农村商业银行',
            '莆田农村商业银行',
            '南昌银行',
            '九江银行',
            '赣州银行',
            '上饶银行',
            '景德镇市商业银行',
            '景德镇农村商业银行',
            '江西洪都农村商业银行',
            '吉安农村商业银行',
            '齐鲁银行',
            '济宁银行',
            '青岛银行',
            '临商银行',
            '枣庄市商业银行',
            '东营市商业银行',
            '潍坊银行',
            '烟台银行',
            '威海市商业银行',
            '齐商银行',
            '泰安市商业银行',
            '日照银行',
            '莱商银行',
            '德州银行',
            '威海农村商业银行',
            '山东荣成农村商业银行',
            '山东莱州农村商业银行',
            '山东寿光农村商业银行',
            '山东邹平农村商业银行',
            '山东张店农村商业银行',
            '山东广饶农村商业银行',
            '山东滕州农村商业银行',
            '烟台农村商业银行',
            '郑州银行',
            '开封市商业银行',
            '洛阳银行',
            '焦作市商业银行',
            '新乡银行',
            '许昌银行',
            '南阳市商业银行',
            '信阳银行',
            '平顶山市商业银行',
            '鹤壁银行',
            '安阳市商业银行',
            '漯河市商业银行',
            '周口银行',
            '河南伊川农村商业银行',
            '三门峡湖滨农村商业银行',
            '河南渑池农村商业银行',
            '河南罗山农村商业银行',
            '许昌魏都农村商业银行',
            '河南新县农村商业银行',
            '河南卢氏农村商业银行',
            '河南鄢陵农村商业银行',
            '河南确山农村商业银行',
            '安阳农村商业银行',
            '河南固始农村商业银行',
            '徽商银行',
            '合肥科技农村商业银行',
            '马鞍山农村商业银行',
            '芜湖扬子农村商业银行',
            '池州九华农村商业银行',
            '安徽无为农村商业银行',
            '安庆独秀农村商业银行',
            '安徽歙县农村商业银行',
            '淮北农村商业银行',
            '安徽颍东农村商业银行',
            '汉口银行',
            '湖北银行',
            '武汉农村商业银行',
            '湖北竹山农村商业银行',
            '湖北利川农村商业银行',
            '湖北秭归农村商业银行',
            '湖北松滋农村商业银行',
            '湖北潜江农村商业银行',
            '湖北竹溪农村商业银行',
            '湖北大冶农村商业银行',
            '湖北宣恩农村商业银行',
            '湖北阳新农村商业银行',
            '湖北恩施农村商业银行',
            '湖北天门农村商业银行',
            '湖北仙桃农村商业银行',
            '湖北襄阳农村商业银行',
            '湖北巴东农村商业银行',
            '湖北东宝农村商业银行',
            '孝感农村商业银行',
            '长沙银行',
            '华融湘江银行',
            '湖南湘江新区农村商业银行',
            '湖南星沙农村商业银行',
            '永州潇湘农村商业银行',
            '湖南炎陵农村商业银行',
            '湖南浏阳农村商业银行',
            '湖南宜章农村商业银行',
            '湖南洪江农村商业银行',
            '常德武陵农村商业银行',
            '湖南宁乡农村商业银行',
            '湖南桂东农村商业银行',
            '广州银行',
            '珠海华润银行',
            '东莞银行',
            '湛江市商业银行',
            '平安银行',
            '广东华兴银行',
            '深圳农村商业银行',
            '广州农村商业银行 ',
            '东莞农村商业银行',
            '佛山顺德农村商业银行',
            '揭阳榕城农村商业银行',
            '广东阳东农村商业银行',
            '河源农村商业银行',
            '江门新会农村商业银行',
            '广东南海农村商业银行',
            '佛山禅城农村商业银行',
            '广西北部湾银行',
            '柳州银行',
            '桂林银行',
            '广西资源农村商业银行',
            '广西田东农村商业银行',
            '广西融安农村商业银行',
            '广西龙胜农村商业银行',
            '重庆银行',
            '重庆三峡银行',
            '重庆农村商业银行',
            '成都银行',
            '绵阳市商业银行',
            '自贡市商业银行',
            '攀枝花市商业银行',
            '德阳银行',
            '泸州市商业银行',
            '乐山市商业银行',
            '南充市商业银行',
            '宜宾市商业银行',
            '凉山州商业银行',
            '遂宁市商业银行',
            '雅安市商业银行',
            '成都农村商业银行',
            '宜宾翠屏农村商业银行',
            '乐山三江农村商业银行',
            '攀枝花农村商业银行',
            '贵阳银行',
            '遵义市商业银行',
            '六盘水市商业银行',
            '安顺市商业银行',
            '富滇银行',
            '曲靖市商业银行',
            '玉溪市商业银行',
            '西安银行',
            '长安银行',
            '临汾尧都农村商业银行',
            '长治农村商业银行 ',
            '兰州银行',
            '甘肃银行',
            '青海银行',
            '西宁农村商业银行',
            '柴达木农村商业银行',
            '宁夏银行',
            '石嘴山银行',
            '宁夏黄河农村商业银行',
            '宁夏平罗农村商业银行',
            '宁夏吴忠农村商业银行',
            '中卫农村商业银行',
            '乌鲁木齐市商业银行',
            '昆仑银行',
            '库尔勒市商业银行',
            '新疆汇和银行',
            '哈密市商业银行',
            '西藏银行',
            '海口农村商业银行',
        ];
        // 清空银行信息
        Bank::where('id', '>', 0)->delete();
        foreach ($banks as $item){
            $bank = new Bank();
            $bank->name = $item;
            $bank->save();
        }
        $this->info("\n初始化银行列表 End");

    }
}