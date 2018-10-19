import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'

import RuleList from '../components/auth/rule/list.vue'
import GroupList from '../components/auth/group/list.vue'
import UserList from '../components/auth/user/list.vue'

import goods from './goods'
import oper from './oper'
import oper_accounts from './oper_account'
import merchant from './merchant'
import members from './members'
import setting from './setting'
import withdraw from './withdraw'
import wallet from './wallet'
import bizer from './bizer'
import Version from './version'
import message from './message'
import OrderList from '../components/order/list'
import TradeRecords from '../components/order/trade_records'

import SettlementPlatfroms from '../components/settlement/platform.vue'

import StatisticsOper from '../components/statistics/oper.vue'
import payment from "./payment";
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

    // 商品模块
    ...goods,
    ...oper,
    ...oper_accounts,
    ...merchant,
    ...members,
    ...setting,
    ...withdraw,
    ...wallet,
    ...bizer,
    ...Version,
    ...payment,
    ...message,

    // 财务模块
    {
        path: '/',
        component: Home,
        children: [
            {path: '/settlement/platforms', component: SettlementPlatfroms, name: 'SettlementPlatfroms'},
        ]
    },
    // 营销报表模块,,
    {
        path: '/',
        component: Home,
        children: [
            {path: '/statistics/oper', component: StatisticsOper, name: 'StatisticsOper'},
        ]
    },
    // 订单列表,
    {
        path: '/',
        component: Home,
        children: [
            {path: '/orders', component: OrderList, name: 'OrderList'},
            {path: '/trade/records', component: TradeRecords, name: 'TradeRecords'},
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
