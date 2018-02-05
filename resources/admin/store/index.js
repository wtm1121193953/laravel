import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

import api from '../../assets/js/api'

import auth from './auth'

let defaultThemes = {
    '深蓝': {
        name: '深蓝',
        color: '#324057',
        menuTextColor: '#bfcbd9',
        menuActiveTextColor: '#409EFF',
    },
    '深灰': {
        name: '深灰',
        color: '#545c64',
        menuTextColor: '#fff',
        menuActiveTextColor: '#ffd04b',
    },
    '亮白': {
        name: '亮白',
        color: '',
        menuTextColor: '',
        menuActiveTextColor: '',
    },
};

// 去除菜单url中的前缀: /admin
let trimMenuUrlPrefix = function(menus, prefix = '/admin'){
    menus.forEach((menu) => {
        if(menu.url && menu.url.indexOf(prefix) === 0){
            menu.url = menu.url.substr(prefix.length);
        }
        if(menu.sub && menu.sub.length > 0){
            trimMenuUrlPrefix(menu.sub)
        }
    })
    return menus;
};

let getFirstMenu = function(menus){
    let firstRoute = '/admin/welcome';
    menus.forEach((menu) => {
        if (menu.sub  && menu.sub[0]  && menu.sub[0].url !== '' ) {
            firstRoute = menu.sub[0].url;
            return false;
        }
    });
    return firstRoute;
};

// 状态存储的 key
const STATE_KEY = 'state';
// 状态本地存储插件, 页面离开时把store中的状态存储到localStorage中
const stateLocalstorePlugin = function(store){
    // store 初始化时调用, 初始化store的数据
    let state = Lockr.get(STATE_KEY);
    if(state){
        store.commit('setTheme', state.theme);
        store.commit('setUser', state.user || null);
        store.commit('setMenus', state.menus || []);
        store.commit('setCurrentMenu', state.currentMenu || getFirstMenu(store.state.menus));
    }

    store.subscribe((mutation, state) => {
        // 每次 mutation 之后调用
        // mutation 的格式为 { type, payload }
        Lockr.set(STATE_KEY, state)
    })
};

export default new Vuex.Store({
    strict: process.env.NODE_ENV !== 'production',
    state: {
        globalLoading: false,
        theme: deepCopy(defaultThemes['深蓝']),
        user: null,
        menus: [],
        currentMenu: null,
    },
    mutations: {
        setGlobalLoading(state, loading){
            state.globalLoading = loading;
        },
        setTheme(state, theme){
            state.theme =  {
                name: theme.name,
                color: theme.color,
                menuTextColor: theme.menuTextColor,
                menuActiveTextColor: theme.menuActiveTextColor,
            };
        },
        setUser(state, user){
            state.user = user;
        },
        setMenus(state, menus){
            state.menus = menus;
        },
        setCurrentMenu(state, currentMenu){
            state.currentMenu = currentMenu;
        }
    },
    actions:{
        openGlobalLoading(context){
            context.commit('setGlobalLoading', true);
        },
        closeGlobalLoading(context){
            context.commit('setGlobalLoading', false);
        },
        resetTheme(context){
            context.commit('setTheme', deepCopy(defaultThemes['深蓝']));
        },
        setThemeByName(context, name){
            let theme = deepCopy(defaultThemes[name]);
            context.commit('setTheme', theme);
        },
        clearUserInfo(context){
            Lockr.rm('userMenuList');
            Lockr.rm('userInfo');
            context.commit('setUser', null);
            context.commit('setMenus', []);
        },
        storeUserInfo(context, {user, menus}){
            menus = trimMenuUrlPrefix(menus);
            Lockr.set('userMenuList', menus);
            Lockr.set('userInfo', user);
            context.commit('setUser', user);
            context.commit('setMenus', menus);
        },
    },
    modules: {
        auth
    },
    plugins: [
        stateLocalstorePlugin
    ]
});
export {
    STATE_KEY
}