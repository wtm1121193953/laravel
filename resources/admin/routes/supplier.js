
import Home from '../components/home'
import SupplierList from '../components/supplier/list.vue'

/**
 * supplier 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'suppliers', component: SupplierList, name: 'SupplierList'},
        ]
    },
];