import Home from '../../components/home'
import SettlementsList from '../components/settlements/list'
import SettlementsPlatform from '../components/settlements/platform'

/**
 * category 模块
 */
export default [
    {
        path: '/cs',
        component: Home,
        children: [
            {path: '/settlements', component: SettlementsList, name: 'SettlementsList'},
            {path: '/settlement/platform/list', component: SettlementsPlatform, name: 'SettlementsPlatform'},
        ]
    },
];