
import Home from '../components/home'
import OperAccountList from '../components/oper-account/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/oper_accounts', component: OperAccountList, name: 'OperAccountList'},
        ]
    },
];