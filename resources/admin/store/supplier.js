
import api from '../../assets/js/api'

const supplier = {
    namespaced: true,
    state: {
        list: [],
        total: 0,
        // some state here
    },
    mutations: {
        setList: (state, list) => state.list = list,
        setTotal: (state, total) => state.total = total,
        // some mutations here
    },
    getters: {
        // some getters here
    },
    actions: {
        getList(context, query){
            api.get('/suppliers', query).then(data => {
                context.commit('setList', data.list)
                context.commit('setTotal', data.total)
            })
        }
        // some other actions here
    },
}

export default supplier;