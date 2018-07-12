
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantAdd from '../components/merchant/add.vue'
import MerchantEdit from '../components/merchant/edit.vue'
import MerchantAddFromPool from '../components/merchant-pool/add-from-merchant-pool.vue'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolAdd from '../components/merchant-pool/add'
import MerchantPoolEdit from '../components/merchant-pool/edit'

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
            {path: '/merchant/add-from-merchant-pool', component: MerchantAddFromPool, name: 'MerchantAddFromPool'},

            {path: '/merchant/pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant/pool/add', component: MerchantPoolAdd, name: 'MerchantPoolAdd'},
            {path: '/merchant/pool/edit', component: MerchantPoolEdit, name: 'MerchantPoolEdit'},

            {path: '/merchant/audits', component: MerchantAuditList, name: 'MerchantAuditList'},
            {path: '/merchant/drafts', component: MerchantDraftList, name: 'MerchantDraftList'},
            {path: '/merchant/detail', component: MerchantDetail, name: 'MerchantDetail'},
        ]
    },
];