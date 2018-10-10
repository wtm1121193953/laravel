import Home from '../components/home'
import BizerList from '../components/bizer/list'
import BizerIdentityAudit from '../components/bizer/identity-audit'

import BizerIdentityList from '../components/bizer/identity'

/**
 * bizer 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/bizer/list', component: BizerList, name: 'BizerList'},
            {path: '/bizer/identity/audit', component: BizerIdentityAudit, name: 'BizerIdentityAudit'},

            {path: '/bizer/identity/list', component: BizerIdentityList, name: 'BizerIdentityList'},
        ]
    },
];