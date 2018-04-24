
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantForm from '../components/merchant/merchant-form.vue'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolForm from '../components/merchant-pool/merchant-form'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/merchants', component: MerchantList, name: 'MerchantList'},
            {path: '/merchants/form', component: MerchantForm, name: 'MerchantForm'},

            {path: '/merchant-pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant-pool/form', component: MerchantPoolForm, name: 'MerchantPoolForm'},
        ]
    },
];