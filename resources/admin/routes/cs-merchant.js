
import Home from '../components/home'
import CsMerchantUnauditList from '../components/cs-merchant/unaudit-list.vue'
import CsMerchantDetail from '../components/cs-merchant/detail.vue'
import CsMerchantEdit from '../components/cs-merchant/edit.vue'
/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/csMerchant/unaudits', component: CsMerchantUnauditList, name: 'CsMerchantUnauditList'},
            {path: '/csMerchant/detail', component: CsMerchantDetail, name: 'CsMerchantDetail'},
            {path: '/csMerchant/edit', component: CsMerchantEdit, name: 'CsMerchantEdit'},


        ]
    },
];