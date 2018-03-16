

/**
 * 引入 工具库 并全局挂载
 */
import _ from 'lodash'
import moment from 'moment'
window._ = _
window.moment = moment

/**
 * 引入 js-cookie 并全局挂载
 */
import Cookies from 'js-cookie'
window.Cookies = Cookies

/**
 * 引入 axios 并全局挂载
 */
import axios from 'axios'
window.axios = axios
// axios.defaults.baseURL = HOST
axios.defaults.timeout = 1000 * 15
axios.defaults.headers.authKey = Lockr.get('authKey')
axios.defaults.headers.sessionId = Lockr.get('sessionId')
axios.defaults.headers['Content-Type'] = 'application/json'

/**
 * 引入 Lockr 并全局挂载
 */
import Lockr from 'lockr'
// 设置Lockr前缀
Lockr.prefix = 'admin_'
// 修复Lockr的rm方法没有使用前缀的bug
var  Lockrm = Lockr.rm;
Lockr.rm = function(key){
    Lockrm(Lockr.prefix + key)
}
window.Lockr = Lockr

// Date对象追加格式化方法
Date.prototype.format = function(fmt){
    if(!fmt){
        fmt = 'yyyy-MM-dd hh:mm:ss'
    }
    if(fmt === 'date') fmt = 'yyyy-MM-dd';
    let o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};

window.deepCopy = function(source){
    if(source === null) return null;
    let obj = {};
    if(typeof source === 'object'){
        for(let key in source){
            let value = source[key];
            if(typeof value === 'object'){
                obj[key] = deepCopy(value)
            }else {
                obj[key] = value
            }
        }
        return obj;
    } else {
        return source;
    }
};

// 如果页面在iframe中, 跳到顶层页面
if(window.top !== window){
    window.top.location = window.location;
}

import TWEEN from '@tweenjs/tween.js'

window.$tween = function $tween (from, to, onUpdate, options){

    let defaultOptions = {
        time: 300,
        easing: TWEEN.Easing.Quadratic.In,
        onUpdate: null,
        onStart: null,
        onStop: null,
    }
    if(typeof onUpdate === 'object'){
        options = onUpdate;
        onUpdate = null;
    }else {
        options = options || {};
        options.onUpdate = onUpdate;
    }
    options.time = options.time || defaultOptions.time;
    options.easing = options.easing || defaultOptions.easing;
    options.onUpdate = options.onUpdate || defaultOptions.onUpdate;
    options.onStart = options.onStart || defaultOptions.onStart;
    options.onStop = options.onStop || defaultOptions.onStop;

    if(typeof from !== 'object'){
        from = {n: from}
        to = {n: to}
        let simpleUpdate = options.onUpdate;
        options.onUpdate = function(data) {
            typeof simpleUpdate === 'function' && simpleUpdate(data.n)
        }
    }

    function animate () {
        if (TWEEN.update()) {
            requestAnimationFrame(animate)
        }
    }
    console.log(options)
    let tween = new TWEEN.Tween(from)
        .to(to, options.time)
        .easing(options.easing)
    if(options.onStart){
        tween.onStart(options.onStart)
    }
    if(options.onUpdate){
        tween.onUpdate(options.onUpdate)
    }
    if(options.onStop){
        tween.onStop(options.onStop)
    }

    tween.start()
    animate()
    return tween;
}
$tween.Easing = TWEEN.Easing;