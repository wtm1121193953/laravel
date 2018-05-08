
import Home from '../components/home'
import OperBizMemberList from '../components/oper-biz-member/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/operBizMembers', component: OperBizMemberList, name: 'OperBizMemberList'},
        ]
    },
];