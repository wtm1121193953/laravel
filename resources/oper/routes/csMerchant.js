
import Home from '../components/home'
import CsMerchantList from '../components/csmerchant/list.vue'
import CsMerchantAdd from '../components/csmerchant/add.vue'
import CsMerchantEdit from '../components/csmerchant/edit.vue'

import CsMerchantAuditList from '../components/csmerchant/audit-list'

import CsMerchantDraftList from '../components/csmerchant/draft-list'
import CsMerchantDetail from '../components/csmerchant/detail'
import MerchantAuditDetail from '../components/csmerchant/unaudit-detail'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/cs/merchants', component: CsMerchantList, name: 'CsMerchantList'},
            {path: '/cs/merchant/add', component: CsMerchantAdd, name: 'CsMerchantAdd'},
            {path: '/cs/merchant/edit', component: CsMerchantEdit, name: 'CsMerchantEdit'},

            {path: '/cs/merchant/audit/list', component: CsMerchantAuditList, name: 'CsMerchantAuditList'},
            {path: '/cs/merchant/audit/detail', component: MerchantAuditDetail, name: 'MerchantAuditDetail'},
            {path: '/cs/merchant/drafts', component: CsMerchantDraftList, name: 'CsMerchantDraftList'},
            {path: '/cs/merchant/detail', component: CsMerchantDetail, name: 'CsMerchantDetail'},
        ]
    },
];