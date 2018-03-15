
import Home from '../components/home'
import CategoryList from '../components/category/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'categories', component: CategoryList, name: 'CategoryList'},
        ]
    },
];