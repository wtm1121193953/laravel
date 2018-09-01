
import Home from '../components/home'
import MemberList from '../components/members/list.vue'
import MemberUserList from '../components/members/user-list.vue'
import MemberIdentity from '../components/members/identity.vue'
import MemberIdentityDetail from '../components/members/identity-detail'
import MemberIdentityEdit from '../components/members/identity-edit'

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
            {path: 'member/list', component: MemberUserList, name: 'MemberUserList'},
            {path: 'member/identity', component: MemberIdentity, name: 'MemberIdentity'},
            {path: 'member/identity/detail', component: MemberIdentityDetail, name: 'MemberIdentityDetail'},
            {path: 'member/identity/edit', component: MemberIdentityEdit, name: 'MemberIdentityEdit'},
        ]
    },
];