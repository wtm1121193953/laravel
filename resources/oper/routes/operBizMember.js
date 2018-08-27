
import Home from '../components/home'
import OperBizMemberList from '../components/oper-biz-member/list.vue'
import OperBizMemberMerchants from '../components/oper-biz-member/merchants'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/operBizMembers', component: OperBizMemberList, name: 'OperBizMemberList'},
            {path: '/operBizMember/merchants', component: OperBizMemberMerchants, name: 'OperBizMemberMerchants'},
        ]
    },
];