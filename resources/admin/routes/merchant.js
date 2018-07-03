
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantUnauditList from '../components/merchant/unaudit-list.vue'
import MerchantDetail from '../components/merchant/detail'

import MerchantCategoryList from '../components/merchant-category/list'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolDetail from '../components/merchant-pool/detail'
import MerchantAuditList from '../components/merchant/audit-record-list'


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

            {path: '/merchant/categories', component: MerchantCategoryList, name: 'MerchantCategoryList'},

            {path: '/merchant/pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant/pool/detail', component: MerchantPoolDetail, name: 'MerchantPoolDetail'},
            {path: '/merchant/recordaudits', component: MerchantAuditList, name: 'MerchantAuditList'},

        ]
    },
];