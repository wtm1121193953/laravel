
import Home from '../../components/home'
import DishesGoodsList from '../components/dishes-goods/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/cs',
        component: Home,
        children: [
            {path: '/dishesGoods', component: DishesGoodsList, name: 'DishesGoodsList'},
        ]
    },
];