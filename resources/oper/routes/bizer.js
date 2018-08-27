import Home from '../components/home'
import bizerList from '../components/bizer/list'
import BizerRecord from '../components/bizer/record'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/bizers', component: bizerList, name: 'bizerList'},
            {path: '/BizerRecord', component: BizerRecord, name: 'BizerRecord'},
        ]
    },
];