
import Home from './components/home.vue'

import Login from './components/login.vue'
import NotFound from './components/page/public/404.vue'
import Error from './components/page/public/error.vue'
import Index from './components/page/public/index.vue'
import Refresh from './components/page/public/refresh.vue'

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
    /*{
        path: '/boss',
        component: Home,
        children: [
            { path: 'auth/group/list', component: GroupList, name: 'GroupList'},
            { path: 'auth/group/add',  component: GroupAdd,  name: 'GroupAdd'},
            { path: 'auth/group/edit', component: GroupEdit, name: 'GroupEdit'},
            { path: 'auth/rule/list',  component: RuleList,  name: 'RuleList'},
            { path: 'auth/rule/add',   component: RuleAdd,   name: 'RuleAdd'},
            { path: 'auth/rule/edit',  component: RuleEdit,  name: 'RuleEdit'},
            { path: 'auth/account/list',  component: AccountList,  name: 'AccountList'},
            { path: 'auth/account/edit',  component: AccountEdit,  name: 'AccountEdit'}
        ]
    },*/

    {path: '*', redirect: '/boss/404'},

];
export default routes
