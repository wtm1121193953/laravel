import Login from './components/login.vue'
import refresh from './components/refresh.vue'
import Home from './components/home.vue'
import ErrorPage from './components/404.vue'
import welcome from './components/welcome.vue'

import RuleList from './components/auth/rule/list.vue'
import GroupList from './components/auth/group/list.vue'
import UserList from './components/auth/user/list.vue'

/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},


    // 权限模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'rules', component: RuleList, name: 'RuleList'},
            {path: 'groups', component: GroupList, name: 'GroupList'},
            {path: 'users', component: UserList, name: 'UserList'},
        ]
    },

    {
        path: '/',
        component: Home,
        children: [
            // demo组件示例
            {path: 'welcome', component: welcome, name: 'welcome'},
            // 刷新组件
            {path: 'refresh', component: refresh, name: 'refresh'},
            // 拦截所有无效的页面到错误页面
            {path: '*', component: ErrorPage, name: 'ErrorPage'},
        ]
    },
    // 拦截所有无效的页面到错误页面
    { path: '*' , component: ErrorPage, name: 'GlobalErrorPage'}

]
export default routes
