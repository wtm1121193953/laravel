
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantDetail from '../components/merchant/detail'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolDetail from '../components/merchant-pool/detail'

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

            {path: '/merchant/pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant/pool/detail', component: MerchantPoolDetail, name: 'MerchantPoolDetail'},
        ]
    },
];