
import Home from '../components/home'
import OperList from '../components/oper/list.vue'

/**
 * oper 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/opers', component: OperList, name: 'OperList'}
        ]
    }
];