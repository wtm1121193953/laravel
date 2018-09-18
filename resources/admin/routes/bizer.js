import Home from '../components/home'
import BizerList from '../components/bizer/list'

/**
 * bizer 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'bizer/list', component: BizerList, name: 'BizerList'},
        ]
    },
];