
import axios from 'axios'
import Lockr from 'lockr'
import Cookies from 'js-cookie'
import _ from 'lodash'
import Vue from 'vue'
import ElementUI from 'element-ui'
import VueRouter from 'vue-router'
import routes from './routes'
import NProgress from 'nprogress'
import 'font-awesome-webpack'


// axios 挂载并初始化
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.timeout = 1000 * 30
window.axios.defaults.headers.authKey = Lockr.get('authKey')
window.axios.defaults.headers.sessionId = Lockr.get('sessionId')
window.axios.defaults.headers['Content-Type'] = 'application/json'

window.Lockr = Lockr; // lockr挂载并初始化
// 设置Lockr前缀
window.Lockr.prefix = 'boss_'
// 修复Lockr的rm方法没有使用前缀的bug
var lockrm = window.Lockr.rm;
window.Lockr.rm = function(key){
    lockrm(Lockr.prefix + key)
}
window.Cookies = Cookies; // 挂载 cookies
window._ = _; // 挂载 lodash


window.Vue = Vue; // 挂载vue
// 初始化VueRouter
const router = new VueRouter({
    mode: 'history',
    base: __dirname,
    routes
})

router.beforeEach((to, from, next) => {
    // const hideLeft = to.meta.hideLeft
    // store.dispatch('showLeftMenu', hideLeft)
    // store.dispatch('showLoading', true)
    NProgress.start()
    next()
})
router.afterEach(transition => {
    NProgress.done()
})
// 挂载路由
window.router = router

Vue.use(ElementUI)
Vue.use(VueRouter)


export default Vue