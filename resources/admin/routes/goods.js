
import Home from '../components/home'
import GoodsList from '../components/goods/list.vue'

/**
 * item 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'goods', component: GoodsList, name: 'GoodsList'},
        ]
    },
];