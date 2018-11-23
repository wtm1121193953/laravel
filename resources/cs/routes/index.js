import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'

import goods from './goods'
import settlements from './settlements'
import categoryList from '../components/category/list'
import subCategoryList from '../components/category/sublist'
import dishesGoods from './dishesGoods'
import wallet from './wallet'

import InviteChannel from '../components/invite-channel'
import PayQrcode from '../components/pay-qrcode'

import InviteStatisticsList from '../components/invite-statistics/list'
import InviteStatisticsDaily from '../components/invite-statistics/daily'

// 订单管理
import OrdersList from '../components/orders/tabs'
import ScanOrderList from '../components/orders/scan/list'

//电子合同管理
import ElectronicContract from '../components/electronic-contract/form'

//系统设置
import SettingMappingUser from '../components/setting/mapping-user.vue'
import DeliverySetting from '../components/setting/delivery_setting'
//setting 商户系统配置
import Setting from '../components/setting/list.vue'
import TpsBind from '../components/setting/tps-bind.vue'
import message from './message'

/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},

    ...goods,
    ...settlements,
    ...dishesGoods,
    ...wallet,
    ...message,

    {
        path: '/',
        component: Home,
        children: [
            {path: '/categories', component: categoryList, name: 'categoryList'},
            {path: '/subCategories', component: subCategoryList, name: 'subCategoryList'},
        ]
    },

    // 我的用户
    {
        path: '/',
        component: Home,
        children: [
            {path: 'invite/statistics/list', component: InviteStatisticsList, name: 'InviteStatisticsList'},
            {path: 'invite/statistics/daily', component: InviteStatisticsDaily, name: 'InviteStatisticsDaily'},
        ]
    },

    // 二维码模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'invite/channel', component: InviteChannel, name: 'InviteChannel'},
            {path: 'pay/qrcode', component: PayQrcode, name: 'PayQrcode'},
        ]
    },

    //订单管理列表
    {
        path: '/',
        component: Home,
        children: [
            {path: 'orders', component: OrdersList, name: 'OrdersList'},
            {path: 'scan/orders', component: ScanOrderList, name: 'ScanOrderList'},
        ]
    },

    // 电子合同管理
    {
        path: '/',
        component: Home,
        children: [
            {path: '/electronic/contract', component: ElectronicContract, name: 'ElectronicContract'},
        ]
    },

    //系统设置
    {
        path: '/',
        component: Home,
        children: [
            {path: 'setting/mapping_user', component: SettingMappingUser, name: 'SettingMappingUser'},
            {path: 'setting', component: Setting, name: 'Setting'},
            {path: 'tps-bind', component: TpsBind, name: 'TpsBind'},
            {path: 'setting/delivery', component: DeliverySetting, name: 'DeliverySetting'},
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
