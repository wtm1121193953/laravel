
import Home from '../components/home'
import CsMerchantUnauditList from '../components/cs-merchant/unaudit-list.vue'
/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/csMerchant/unaudits', component: CsMerchantUnauditList, name: 'CsMerchantUnauditList'},


        ]
    },
];