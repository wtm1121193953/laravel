
import Home from '../components/home'
import OperList from '../components/oper/list.vue'
import OperAdd from '../components/oper/add'
import OperEdit from '../components/oper/edit'
import OperDetail from '../components/oper/detail'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/opers', component: OperList, name: 'OperList'},
            {path: '/oper/add', component: OperAdd, name: 'OperAdd'},
            {path: '/oper/edit', component: OperEdit, name: 'OperEdit'},
            {path: '/oper/detail', component: OperDetail, name: 'OperDetail'},
        ]
    },
];