
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantForm from '../components/merchant/merchant-form.vue'

import MerchantPool from '../components/merchant-pool/list'
import MerchantPoolAdd from '../components/merchant-pool/add'
import MerchantPoolEdit from '../components/merchant-pool/edit'

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

            {path: '/merchant/pool', component: MerchantPool, name: 'MerchantPool'},
            {path: '/merchant/pool/add', component: MerchantPoolAdd, name: 'MerchantPoolAdd'},
            {path: '/merchant/pool/edit', component: MerchantPoolEdit, name: 'MerchantPoolEdit'},
        ]
    },
];