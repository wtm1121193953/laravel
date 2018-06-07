import Login from '../components/login.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'

import OrdersList from '../components/orders/list.vue'
/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},

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
