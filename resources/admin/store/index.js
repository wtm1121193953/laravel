import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

import api from '../../assets/js/api'

// 页面离开时把store中的状态存储到localStorage中
const STATE_KEY = 'state';
// 状态本地存储插件
const stateLocalstorePlugin = function(store){
    let state = Lockr.get(STATE_KEY);
    if(state){
        store.commit('setTheme', state.theme);
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
        theme: {
            name: '深蓝',
            color: '#324057',
            menuTextColor: '#bfcbd9',
            menuActiveTextColor: '',
        },
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
        resetTheme(state){
            state.theme = {
                name: '深蓝',
                color: '#324057',
                menuTextColor: '#bfcbd9',
                menuActiveTextColor: '#409EFF',
            }
        },
        setThemeByName(state, name){
            state.theme.name = name;
            switch (name){
                case '深蓝':
                    state.theme.color = '#324057';
                    state.theme.menuTextColor = '#bfcbd9';
                    state.theme.menuActiveTextColor = '#409EFF';
                    break;
                case '深灰':
                    state.theme.color = '#545c64';
                    state.theme.menuTextColor = '#fff';
                    state.theme.menuActiveTextColor = '#ffd04b';
                    break;
                case '亮白':
                    state.theme.color = '';
                    state.theme.menuTextColor = '';
                    state.theme.menuActiveTextColor = '';
                    break;
            }
        },
    },
    actions:{
    },
    plugins: [
        stateLocalstorePlugin
    ]
});
export {
    STATE_KEY
}