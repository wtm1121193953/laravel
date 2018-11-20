
import Home from '../../components/home'
import DishesCategoryList from '../components/dishes-category/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/cs',
        component: Home,
        children: [
            {path: '/dishesCategories', component: DishesCategoryList, name: 'DishesCategoryList'},
        ]
    },
];