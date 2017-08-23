
import Home from './components/home.vue'

import Login from './components/login.vue'
import NotFound from './components/page/public/404.vue'
import Error from './components/page/public/error.vue'
import Index from './components/page/public/index.vue'
import Refresh from './components/page/public/refresh.vue'

import UserList from './components/page/user/list.vue'
import UserAdd from './components/page/user/add.vue'
import UserEdit from './components/page/user/edit.vue'

import RuleList from './components/page/rule/list.vue'
import RuleAdd from './components/page/rule/add.vue'
import RuleEdit from './components/page/rule/edit.vue'

/**
 * meta参数解析
 * hideLeft: 是否隐藏左侧菜单，单页菜单为true
 * module: 菜单所属模块
 * menu: 所属菜单，用于判断三级菜单是否显示高亮，如菜单列表、添加菜单、编辑菜单都是'menu'，用户列表、添加用户、编辑用户都是'user'，如此类推
 */
const routes = [

    // 该路径最终需要指向到首页
    // {path: '/boss.html', component: LinkList, name: 'LinkList'},
    {path: '/boss.html', redirect: '/boss/index'},
    {path: '/', redirect: '/boss/index'},

    {path: '/login', redirect: '/boss/login'},
    {path: '/boss/login', component: Login, name: 'Login'},

    {
        path: '/boss',
        component: Home,
        children: [
            {path: '/refresh', component: Refresh, name: 'Refresh'},
            {path: 'index', component: Index, name: 'Index'},
            {path: '404', component: NotFound, name: 'NotFound'},
            {path: 'error', component: Error, name: 'Error'},
        ]
    },

    // 权限模块
    {
        path: '/boss',
        component: Home,
        children: [
            { path: 'user/list', component: UserList, name: 'UserList'},
            { path: 'user/add',  component: UserAdd,  name: 'UserAdd'},
            { path: 'user/edit', component: UserEdit, name: 'UserEdit'},
            /*{ path: 'group/list',  component: AccountList,  name: 'AccountList'},
            { path: 'group/add',  component: AccountEdit,  name: 'AccountEdit'},
            { path: 'group/edit',  component: AccountEdit,  name: 'AccountEdit'},*/
            { path: 'rule/list',  component: RuleList,  name: 'RuleList'},
            { path: 'rule/add',   component: RuleAdd,   name: 'RuleAdd'},
            { path: 'rule/edit',  component: RuleEdit,  name: 'RuleEdit'},
        ]
    },

    {path: '*', redirect: '/boss/404'},

];
export default routes
