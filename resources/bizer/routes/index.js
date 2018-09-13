import Login from '../components/login.vue'
import Register from '../components/register.vue'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'



import merchant from './merchant'
import oper from './oper'
import order from './order'
/**
 *
 */
const routes = [

    {path: '/login', component: Login, name: 'Login'},
    {path: '/register', component: Register, name: 'Register'},
    {
        path: '/',
        component: Home,
        children: [
            ...oper,
            ...order,
            ...merchant,
            // demo组件示例
            {path: '/', component: welcome, name: 'welcomePage'},
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
