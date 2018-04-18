
import Home from '../components/home'
import GoodsList from '../components/goods/list.vue'
import GoodsAdd from '../components/goods/add'
import GoodsEdit from '../components/goods/edit'


/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            //商品管理列表
            {path: '/goods', component: GoodsList, name: 'GoodsList'},
            {path: '/goods/add', component: GoodsAdd, name: 'GoodsAdd'},
            {path: '/goods/edit', component: GoodsEdit, name: 'GoodsEdit'},

        ]
    },
];