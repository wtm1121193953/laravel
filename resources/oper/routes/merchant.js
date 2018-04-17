
import Home from '../components/home'
import MerchantList from '../components/merchant/list.vue'
import MerchantForm from '../components/merchant/merchant-form.vue'

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
        ]
    },
];