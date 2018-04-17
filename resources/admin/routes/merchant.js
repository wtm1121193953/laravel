
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantDetail from '../components/merchant/detail'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/merchants', component: MerchantList, name: 'MerchantList'},
            {path: '/merchant/detail', component: MerchantDetail, name: 'MerchantDetail'},
        ]
    },
];