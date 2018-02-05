
import api from '../../assets/js/api'

const auth = {
    namespaced: true,
    state: {
        rules: [],
        ruleTree: [],
        rulesIdMapping: {},
        rulePids: [],
        groups: [],
        users: [],
    },
    mutations: {
        setRules(state, rules){
            state.rules = rules;
        },
        setRuleTree(state, tree){
            state.ruleTree = tree;
        },
        setRulesIdMapping(state, mapping){
            state.rulesIdMapping = mapping;
        },
        setRulePids(state, pids){
            state.rulePids = pids;
        },
        setGroups(state, groups){
            state.groups = groups;
        },
        setUsers(state, users){
            state.users = users;
        }
    },
    actions: {
        mapRulesId(context, rules){
            let idMapping = {};
            let pids = [];
            rules.forEach(item => {
                idMapping[item.id] = item;
                pids.push(item.pid)
            });
            context.commit('setRulesIdMapping', idMapping);
            context.commit('setRulePids', pids)
        },
        getRules(context){
            api.get('/rules').then((res) => {
                api.handlerRes(res).then(data => {
                    context.commit('setRules', data.list);
                    context.dispatch('mapRulesId', data.list)
                })
            })
        },
        getRuleTree(context){
            api.get('/rules/tree').then((res) => {
                api.handlerRes(res).then(data => {
                    context.commit('setRuleTree', data.tree);
                    context.dispatch('mapRulesId', data.list)
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