import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

import api from '../../assets/js/api'

// 页面离开时把store中的状态存储到localStorage中
const STATE_KEY = 'state';

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
// 状态本地存储插件
const stateLocalstorePlugin = function(store){
    let state = Lockr.get(STATE_KEY);
    if(state){
        store.commit('setTheme', state.theme);
        store.commit('setUser', state.user || null);
        store.commit('setMenus', state.menus || []);
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
            Lockr.set('userMenuList', menus);
            Lockr.set('userInfo', user);
            context.commit('setUser', user);
            context.commit('setMenus', menus);
        }
    },
    plugins: [
        stateLocalstorePlugin
    ]
});
export {
    STATE_KEY
}