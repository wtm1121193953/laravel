import Home from '../components/home'
import SettlementsList from '../components/settlements/list'
import SettlementsPlatform from '../components/settlements/platform'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/settlements', component: SettlementsList, name: 'SettlementsList'},
            {path: '/settlements/platform', component: SettlementsPlatform, name: 'SettlementsPlatform'},
        ]
    },
];