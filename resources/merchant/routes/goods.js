
import Home from '../components/home'
import GoodsList from '../components/goods/list.vue'

import OrdersList from '../components/orders/list.vue'

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

            //订单管理列表
            {path: '/orders', component: OrdersList, name: 'OrdersList'},
        ]
    },
];