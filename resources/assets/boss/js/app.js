import Vue from './bootstrap';

import App from './components/app'

import bus from '../../js/bus';

window.baseApiUrl = '/api/boss/';
import api from '../../js/api';

window.bus = bus;
window.api = api;

// 给所有的vue示例挂载一个刷新页面的方法
Vue.prototype.$refresh = function(name){
    router.replace({path: '/refresh', query: {name: name}})
};

const app = new Vue({
    el: '#app',
    template: '<App/>',
    router,
    components: {App}
});
