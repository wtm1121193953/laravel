import Home from '../components/home'
import bizerList from '../components/bizer/list'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/bizers', component: bizerList, name: 'bizerList'},
        ]
    },
];