
import api from '../../assets/js/api'

const auth = {
    namespaced: true,
    state: {
        rules: [],
        groups: [],
        users: [],
    },
    mutations: {
        setRules(state, rules){
            state.rules = rules;
        },
        setGroups(state, groups){
            state.groups = groups;
        },
        setUsers(state, users){
            state.users = users;
        }
    },
    actions: {
        getRules(context){
            api.get('/rules').then((res) => {
                api.handlerRes(res).then(data => {
                    context.commit('setRules', data.list)
                })
            })
        },
        getGroups(context){
            api.get('/groups').then(res => {
                api.handlerRes(res).then(data => {
                    context.commit('setGroups', data.list)
                })
            })
        },
        getUsers(context){
            api.get('/users').then(res => {
                api.handlerRes(res).then(data => {
                    context.commit('setUsers', data.list)
                })
            })
        }
    },
    getters: {

    }
}

export default auth