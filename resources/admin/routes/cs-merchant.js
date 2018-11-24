
import Home from '../components/home'
import CsMerchants from '../components/cs-merchant/supermarkets.vue'
import CsMerchantDetail from '../components/cs-merchant/detail.vue'
import CsMerchantEdit from '../components/cs-merchant/edit.vue'

import CsCategories from '../components/cs-category/list'

import CsMerchantUnaudit from '../components/cs-merchant/audit-list'
import CsMerchantUnauditDetail from '../components/cs-merchant/unaudit-detail'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'cs/categories', component: CsCategories, name: 'CsCategories'},
        ]
    },
    {
        path: '/',
        component: Home,
        children: [
            {path: 'cs/merchants', component: CsMerchants, name: 'CsMerchants'},
            {path: 'cs/merchant/detail', component: CsMerchantDetail, name: 'CsMerchantDetail'},
            {path: 'cs/merchant/edit', component: CsMerchantEdit, name: 'CsMerchantEdit'},
            {path: '/cs/merchants', component: CsMerchants, name: 'CsMerchants'},
            {path: '/cs/merchant/detail', component: CsMerchantDetail, name: 'CsMerchantDetail'},
            {path: '/cs/merchant/edit', component: CsMerchantEdit, name: 'CsMerchantEdit'},
            {path: '/cs/merchant/unaudits', component: CsMerchantUnaudit, name: 'CsMerchantUnaudit'},
            {path: '/cs/merchant/unaudits/detail', component: CsMerchantUnauditDetail, name: 'CsMerchantUnauditDetail'},


        ]
    },
];