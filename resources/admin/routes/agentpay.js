import Home from '../components/home'
import AgentpayList from '../components/agentpay/list'
import AgentpayAdd from '../components/agentpay/add'
import AgentpayEdit from '../components/agentpay/edit'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/agentpays', component: AgentpayList, name: 'AgentpayList'},
            {path: '/agentpay/add', component: AgentpayAdd, name: 'AgentpayAdd'},
            {path: '/agentpay/edit', component: AgentpayEdit, name: 'AgentpayEdit'},
        ]
    },
];