
import Home from '../components/home'
import ItemList from '../components/item/list.vue'

/**
 * item 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'items', component: ItemList, name: 'ItemList'},
        ]
    },
];