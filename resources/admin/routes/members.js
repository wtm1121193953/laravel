
import Home from '../components/home'
import MemberList from '../components/members/list.vue'

// 换绑
import ChangeBindTab from '../components/members/change-bind/tab'
import InviteUsersList from '../components/members/change-bind/invite-users-list'
import ChangeBindList from '../components/members/change-bind/change-bind-list'
import ChangeBindRecordList from '../components/members/change-bind/change-bind-record-list'

/**
 * 会员管理 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'members', component: MemberList, name: 'MemberList'},

            {path: 'member/changBind', component: ChangeBindTab, name: 'ChangeBindTab'},
            {path: 'member/inviteUsersList', component: InviteUsersList, name: 'InviteUsersList'},
            {path: 'member/changeBindList', component: ChangeBindList, name: 'ChangeBindList'},
            {path: 'member/changeBindRecordList', component: ChangeBindRecordList, name: 'ChangeBindRecordList'},
        ]
    },
];