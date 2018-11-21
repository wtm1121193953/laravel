import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'

import merchant from './merchant'
import csMerchant from "./csMerchant";
import settlements from './settlements'
import operBizMembers from './operBizMember'
import bizer from './bizer'
import wallet from './wallet'

import OrderList from '../components/order/list'
import CsOrderList from '../components/order/cs/tabs'

import InviteChannelList from '../components/invite-channel/list'
import InviteRecords from '../components/invite-record/list'

import InviteStatisticsDaily from '../components/invite-statistics/daily'
import message from './message'

import GoodsList from '../components/cs_goods/list'

//系统设置
import SettingMappingUser from '../components/setting/mapping-user'

//系统设置
import TpsBind from '../components/setting/tps-bind'
import MemberList from '../components/member/list'
import MemberStatistics from '../components/member/statistics'

/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},

    ...merchant,
    ...csMerchant,
    ...settlements,
    ...operBizMembers,
    ...wallet,
    ...bizer,
    ...message,
    // 订单模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'orders', component: OrderList, name: 'OrderList'},
            {path: 'cs/orders', component: CsOrderList, name: 'CsOrderList'},
        ]
    },

    // 商品模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'cs/goods', component: GoodsList, name: 'GoodsList'},
        ]
    },


    // 我的用户
    {
        path: '/',
        component: Home,
        children: [
            {path: 'invite/statistics/daily', component: InviteStatisticsDaily, name: 'InviteStatisticsDaily'},
            {path: 'member/index', component: MemberList, name: 'MemberList'},
            {path: 'member/statistics', component: MemberStatistics, name: 'MemberStatistics'},
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
        ]
    },
    
    //系统设置sysconfig
    {
        path: '/',
        component: Home,
        children: [
            {path: 'tps-bind', component: TpsBind, name: 'TpsBind'},
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
