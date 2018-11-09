
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantUnauditList from '../components/merchant/unaudit-list.vue'
import MerchantDetail from '../components/merchant/detail'
import MerchantEdit from '../components/merchant/edit'

import MerchantCategoryList from '../components/merchant-category/list'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolDetail from '../components/merchant-pool/detail'
import MerchantAuditList from '../components/merchant/audit-record-list'

import MerchantPilotList from '../components/pilot-merchant/list'
import MerchantPilotDetail from '../components/pilot-merchant/detail'
import MerchantPilotEdit from '../components/pilot-merchant/edit'

import ElectronicContract from '../components/merchant/electronic-contract/list'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/merchants', component: MerchantList, name: 'MerchantList'},
            {path: '/merchant/unaudits', component: MerchantUnauditList, name: 'MerchantUnauditList'},
            {path: '/merchant/detail', component: MerchantDetail, name: 'MerchantDetail'},
            {path: '/merchant/edit', component: MerchantEdit, name: 'MerchantEdit'},

            {path: '/merchant/categories', component: MerchantCategoryList, name: 'MerchantCategoryList'},

            {path: '/merchant/pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant/pool/detail', component: MerchantPoolDetail, name: 'MerchantPoolDetail'},
            {path: '/merchant/recordaudits', component: MerchantAuditList, name: 'MerchantAuditList'},

            {path: '/merchant/pilots', component: MerchantPilotList, name: 'MerchantPilotList'},
            {path: '/merchant/pilot/detail', component: MerchantPilotDetail, name: 'MerchantPilotDetail'},
            {path: '/merchant/pilot/edit', component: MerchantPilotEdit, name: 'MerchantPilotEdit'},

            {path: '/merchant/electronic/contracts', component: ElectronicContract, name: 'ElectronicContract'},

        ]
    },
];