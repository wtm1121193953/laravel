import axios from 'axios';

window.baseApiUrl = window.baseApiUrl || '';
const CODE_OK = 0;
const CODE_UN_LOGIN = 10003;

function handlerRes(res) {
    return new Promise((resolve, reject) => {
        if (res && res.code === CODE_OK) {
            resolve(res.data);
        } else {
            switch (res.code) {
                case CODE_UN_LOGIN:
                    Lockr.rm('userInfo');
                    Lockr.rm('menus');
                    router.push('/login');
                    bus.$message.error('您的登录信息已失效, 请先登录');
                    break;
                default:
                    if(!res.disableErrorMessage){
                        bus.$message.error(res.message || '操作失败');
                    }
                    break;
            }
            reject(res);
        }
    })
}

function handlerNetworkError(error) {
    console.log('network error: ', error);
    bus.$message.error('请求超时，请检查网络')
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

function get(url, params) {
    let options = {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        params: params,
    };
    url = getRealUrl(url);
    return axios.get(url, options).then(res => {
        return res.data
    }).catch(handlerNetworkError)
}

function post(url, params) {
    let options = {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    };
    url = getRealUrl(url);
    return axios.post(url, params, options).then(res => {
        return res.data
    }).catch(handlerNetworkError)
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
    handlerRes
}

// export const requestLogin = params => { return axios.post(`${base}/login`, params).then(res => res.data); };
