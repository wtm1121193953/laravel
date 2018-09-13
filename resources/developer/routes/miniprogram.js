
import Home from '../components/home'
import MiniprogramList from '../components/miniprogram/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/miniprograms', component: MiniprogramList, name: 'MiniprogramList'},
        ]
    },
];