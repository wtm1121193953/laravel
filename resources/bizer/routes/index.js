import Login from '../components/login.vue'
import LoginForm from '../components/login/login-form'
import RegisterForm from '../components/login/register-form'
import refresh from '../components/refresh.vue'
import Home from '../components/home.vue'
import ErrorPage from '../components/404.vue'
import welcome from '../components/welcome.vue'



import merchant from './merchant'
import oper from './oper'
import order from './order'
import walletAndWithdrew from './wallet-and-withdraw'

const routes = [
    {
        path: '/',
        component: Login,
        children: [
            {path: '/', component: LoginForm, name: 'Login'},
            {path: 'login', component: LoginForm, name: 'LoginForm'},
            {path: '/register', component: RegisterForm, name: 'RegisterForm'},
        ]
    },
    {
        path: '/',
        component: Home,
        children: [
            ...oper,
            ...order,
            ...merchant,
            ...walletAndWithdrew,
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
