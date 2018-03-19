<?php

use App\Modules\Goods\Category;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(Category::class)->create(['name' => '男装',]);
        factory(Category::class)->create(['name' => '女装',]);
        factory(Category::class)->create(['name' => '笔记本',]);
        factory(Category::class)->create(['name' => '台式电脑',]);
        factory(Category::class)->create(['name' => '手表',]);
        factory(Category::class)->create(['name' => '水果',]);
        factory(Category::class)->create(['name' => '小吃',]);
        factory(Category::class)->create(['name' => '食品',]);
    }
}
