
import './bootstrap'

import 'babel-polyfill'
import NProgress from 'nprogress'

import Vue from 'vue'
window.Vue = Vue;

import store from './store'
window.store = store;

import routes from './routes/index'
import VueRouter from 'vue-router'
const router = new VueRouter({
    mode: 'history',
    base: '/oper',
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
router.afterEach(() => {
    store.commit('setGlobalLoading', false)
    NProgress.done()
})
window.router = router
Vue.use(VueRouter)

import ElementUI from 'element-ui'
Vue.use(ElementUI);

import VueAMap from 'vue-amap'
Vue.use(VueAMap);
window.VueAMap = VueAMap;
console.log(VueAMap);
VueAMap.initAMapApiLoader({
    key: '315bde9730e45b8b095517d052361f11',
    plugin: ['AMap.Autocomplete', 'AMap.PlaceSearch', 'AMap.Scale', 'AMap.OverView', 'AMap.ToolBar', 'AMap.MapType', 'AMap.PolyEditor', 'AMap.CircleEditor'],
    // 默认高德 sdk 版本为 1.4.4
    v: '1.4.4'
});

import page from './components/page'
Vue.component('page', page)

// single image upload
import ImageUpload from '../assets/components/upload/image-upload'
Vue.component(ImageUpload.name, ImageUpload)

import quillEditorPlugin from './quill-editor-plugin'
Vue.use(quillEditorPlugin.VueQuillEditor, quillEditorPlugin.globalOptions)

window.baseApiUrl = '/api/oper/'
import api from '../assets/js/api'
window.api = api;
Vue.prototype.$api = api;

import App from './App.vue'
new Vue({
    el: '#app',
    template: '<App/>',
    router,
    store,
    components: {App}
// render: h => h(Login)
}).$mount('#app')
