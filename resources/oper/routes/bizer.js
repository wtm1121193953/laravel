import Home from '../components/home'
import bizerList from '../components/bizer/list'
// import BizerMerchants from '../components/bizer/merchants'
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
            // {path: '/bizers/BizerMerchants', component: BizerRecord, name: 'BizerMerchants'},
            {path: '/BizerRecord', component: BizerRecord, name: 'BizerRecord'},
        ]
    },
];