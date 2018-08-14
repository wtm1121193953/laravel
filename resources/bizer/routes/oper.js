import OperList from '../components/oper/list.vue'
import record from '../components/oper/record.vue'
/**
 * oper 模块
 */
export default [
    {path: '/opers', component: OperList, name: 'OperList'},
    {path: '/opers/record', component: record, name: 'record'},
];