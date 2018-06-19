import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'

import goods from './goods'
import settlements from './settlements'

import InviteChannel from '../components/invite-channel'

import InviteStatisticsDaily from '../components/invite-statistics/daily'

import OrdersList from '../components/orders/list.vue'
/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},

    ...goods,
    ...settlements,

    // 我的会员
    {
        path: '/',
        component: Home,
        children: [
            {path: 'invite/statistics/daily', component: InviteStatisticsDaily, name: 'InviteStatisticsDaily'},
        ]
    },

    // 二维码模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'invite/channel', component: InviteChannel, name: 'InviteChannel'},
        ]
    },

    //订单管理列表
    {
        path: '/',
        component: Home,
        children: [
            {path: 'orders', component: OrdersList, name: 'OrdersList'},
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
