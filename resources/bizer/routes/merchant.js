
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantAdd from '../components/merchant/add.vue'
import MerchantEdit from '../components/merchant/edit.vue'

import MerchantAuditList from '../components/merchant/audit-list'

import MerchantDraftList from '../components/merchant/draft-list'
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
            {path: '/merchant/add', component: MerchantAdd, name: 'MerchantAdd'},
            {path: '/merchant/edit', component: MerchantEdit, name: 'MerchantEdit'},

            {path: '/merchant/audits', component: MerchantAuditList, name: 'MerchantAuditList'},
            {path: '/merchant/drafts', component: MerchantDraftList, name: 'MerchantDraftList'},
            {path: '/merchant/detail', component: MerchantDetail, name: 'MerchantDetail'},
        ]
    },
];