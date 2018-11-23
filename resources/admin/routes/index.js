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
import csMerchant from './cs-merchant'
import members from './members'
import setting from './setting'
import withdraw from './withdraw'
import wallet from './wallet'
import bizer from './bizer'
import Version from './version'
import message from './message'
import OrderList from '../components/order/list'
import TradeRecords from '../components/order/trade_records'
import TradeRecordsDaily from '../components/order/trade_records_daily';

import SettlementPlatfroms from '../components/settlement/platform.vue'

import AllSettlementPlatfroms from '../components/settlement/all-platform'
import CsSettlementPlatforms from '../components/settlement/cs-supermarket-platform'

import SettlementPlatfromBatches from '../components/settlementPlatformBatches/list'

import StatisticsOper from '../components/statistics/oper.vue'
import StatisticsMerchant from '../components/statistics/merchant.vue'
import StatisticsUser from '../components/statistics/user.vue'
import StatisticsInviteList from '../components/statistics/invite-list'

import payment from "./payment";
import agentpay from "./agentpay";
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
    ...csMerchant,
    ...members,
    ...setting,
    ...withdraw,
    ...wallet,
    ...bizer,
    ...Version,
    ...payment,
    ...message,
    ...agentpay,

    // 财务模块
    {
        path: '/',
        component: Home,
        children: [
            // 财务模块--人工
            {path: '/settlement/platforms', component: SettlementPlatfroms, name: 'SettlementPlatfroms'},
            // 财务模块--全部
            {path: '/settlement/allPlatforms', component: AllSettlementPlatfroms, name: 'AllSettlementPlatfroms'},
            // 财务模块--超市商户结算
            {path: '/settlement/csPlatforms', component: CsSettlementPlatforms, name: 'CsSettlementPlatfroms'},
            //自动结算模块
            {path: '/settlementPlatformBatches/list', component: SettlementPlatfromBatches, name: 'SettlementPlatfromBatches'},
        ]
    },

    // 营销报表模块,,
    {
        path: '/',
        component: Home,
        children: [
            {path: '/statistics/oper', component: StatisticsOper, name: 'StatisticsOper'},
            {path: '/statistics/merchant', component: StatisticsMerchant, name: 'StatisticsMerchant'},
            {path: '/statistics/user', component: StatisticsUser, name: 'StatisticsUser'},
            {path: '/statistics/invite/list', component: StatisticsInviteList, name: 'StatisticsInviteList'},
        ]
    },
    // 订单列表,
    {
        path: '/',
        component: Home,
        children: [
            {path: '/orders', component: OrderList, name: 'OrderList'},
            {path: '/trade/records', component: TradeRecords, name: 'TradeRecords'},
            {path: '/trade/records_daily', component: TradeRecordsDaily, name: 'TradeRecordsDaily'},
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
