
import Home from '../components/home'
import MemberList from '../components/members/list.vue'

/**
 * item 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'members', component: MemberList, name: 'MemberList'},
        ]
    },
];