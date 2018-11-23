
import Home from '../components/home'
import CsMerchants from '../components/cs-merchant/list.vue'
import CsMerchantDetail from '../components/cs-merchant/detail.vue'
import CsMerchantEdit from '../components/cs-merchant/edit.vue'

import CsCategories from '../components/cs-category/list'

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
        ]
    },
];