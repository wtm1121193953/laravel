
import Home from '../components/home'
import DemoList from '../components/demo/list.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'demos', component: DemoList, name: 'DemoList'},
        ]
    },
];