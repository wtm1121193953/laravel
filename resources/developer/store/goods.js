
import api from '../../assets/js/api'

const goods = {
    namespaced: true,
    state: {
        suppliers: [], // 全部供应商列表
        categories: [], // 全部商品分类列表

    },
    mutations: {
        setSuppliers(state, suppliers){
            state.suppliers = suppliers;
        },
        setCategories(state, categories){
            state.categories = categories;
        },
    },
    getters: {
        enableSuppliers(state){
            return state.suppliers.filter(item => item.status === 1)
        },
        enableCategories(state){
            return state.categories.filter(item => item.status === 1)
        }
    },
    actions: {
        getAllSuppliers(context){
            api.get('/suppliers/all').then(data => {
                context.commit('setSuppliers', data.list)
            })
        },
        getAllCategories(context){
            api.get('/categories/all', {status: 1}).then(data => {
                context.commit('setCategories', data.list)
            })
        },
    },
}

export default goods