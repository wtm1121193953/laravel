import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'

import merchant from './merchant'
import settlements from './settlements'
import operBizMembers from './operBizMember'

import OrderList from '../components/order/list'

import InviteChannelList from '../components/invite-channel/list'
import InviteRecords from '../components/invite-record/list'

import InviteStatisticsDaily from '../components/invite-statistics/daily'

//系统设置
import SettingMappingUser from '../components/setting/mapping-user'
import SettingPayToPlantForm from '../components/setting/pay-to-platform'

/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},

    ...merchant,
    ...settlements,
    ...operBizMembers,

    // 订单模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'orders', component: OrderList, name: 'OrderList'},
        ]
    },


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
            {path: 'invite-channel', component: InviteChannelList, name: 'InviteChannelList'},
            {path: 'invite-records', component: InviteRecords, name: 'InviteRecords'},
        ]
    },

    //系统设置
    {
        path: '/',
        component: Home,
        children: [
            {path: 'setting/mapping_user', component: SettingMappingUser, name: 'SettingMappingUser'},
            {path: 'setting/pay_to_platform', component: SettingPayToPlantForm, name: 'SettingPayToPlantForm'},
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
