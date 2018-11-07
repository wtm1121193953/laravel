import axios from 'axios';

import {Message} from 'element-ui'

window.baseApiUrl = window.baseApiUrl || '';
const CODE_OK = 0;
const CODE_UN_LOGIN = 10003;

class ResponseError {
    constructor(response){
        this.response = response;
    }
}

function handlerRes(res) {
    if (res && res.code === CODE_OK) {
        return res.data;
    } else {
        return Promise.reject(new ResponseError(res));
    }
}

function handlerError(error) {
    if(error instanceof ResponseError){
        let res = error.response;
        if(res && res.code){
            switch (res.code) {
                case CODE_UN_LOGIN:
                    Lockr.rm('userInfo');
                    Lockr.rm('menus');
                    router.push('/login');
                    Message.error('您的登录信息已失效, 请先登录');
                    break;
                default:
                    console.log('接口返回错误信息:', res);
                    if(!res.disableErrorMessage){
                        Message.error(res.message)
                    }
                    break;
            }
        }else {
            console.log('未知错误:', res);
        }
    }else {
        console.error('network error: ', error);
        Message.error('请求超时，请检查网络')
    }
}

function getRealUrl(url) {
    if(url.indexOf(window.baseApiUrl) === 0){
        return url;
    }
    if(url.indexOf('/') === 0){
        url = url.substr(1);
    }
    return window.baseApiUrl + url
}

function get(url, params, defaultHandlerRes=true) {
    let options = {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        params: params,
    };
    url = getRealUrl(url);
    let promise = axios.get(url, options).then(res => {
        let result = res.data;
        if(defaultHandlerRes){
            return handlerRes(result);
        }else {
            return result;
        }
    });
    promise.catch(handlerError);
    return promise;
}

function post(url, params, defaultHandlerRes=true) {
    let options = {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        timeout: 1000 * 30,
    };
    url = getRealUrl(url);
    let promise = axios.post(url, params, options).then(res => {
        let result = res.data;
        if(defaultHandlerRes){
            return handlerRes(result);
        }else {
            return result;
        }
    });
    promise.catch(handlerError);
    return promise;
}

function mockData(data) {
    return new Promise((resolve, reject) => {
        resolve(data);
    })
}

export default {
    get,
    post,
    mockData,
    handlerRes,
}