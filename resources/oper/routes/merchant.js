
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/merchants', component: MerchantList, name: 'MerchantList'},
        ]
    },
];