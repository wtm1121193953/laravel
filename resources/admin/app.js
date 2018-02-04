
import './bootstrap'

import 'babel-polyfill'
import Vue from 'vue'
import App from './App.vue'
import ElementUI from 'element-ui'
import routes from './routes'
import VueRouter from 'vue-router'
import NProgress from 'nprogress'

import 'nprogress/nprogress.css'


import store from './store'
window.store = store;

window.baseApiUrl = '/api/admin/'
const router = new VueRouter({
    mode: 'history',
    base: '/admin',
    routes
})

router.beforeEach((to, from, next) => {
    store.commit('setGlobalLoading', true)
    NProgress.start()
    // 处理服务器重定向到指定页面时在浏览器返回页面为空的问题
    if(to.query._from){
        store.commit('setGlobalLoading', false)
        NProgress.done()
        next(to.query._from);
    }else {
        next()
    }
})

router.afterEach(transition => {
    store.commit('setGlobalLoading', false)
    NProgress.done()
})

import SingleImageUpload from '../assets/components/upload/single-image-upload'
Vue.component(SingleImageUpload.name, SingleImageUpload)

Vue.use(ElementUI)
Vue.use(VueRouter)

window.Vue = Vue;
window.router = router

// window.HOST = HOST
window.HOST = '/'
window.pageSize = 15

import api from '../assets/js/api'
window.api = api;
Vue.prototype.$api = api;

new Vue({
    el: '#app',
    template: '<App/>',
    router,
    store,
    components: {App}
// render: h => h(Login)
}).$mount('#app')
