import Home from '../components/home'
import SettlementsList from '../components/settlements/list'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/settlements', component: SettlementsList, name: 'SettlementsList'},
        ]
    },
];