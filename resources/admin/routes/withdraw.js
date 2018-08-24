import Home from '../components/home'
// 提现管理
import WithdrawDashboard from '../components/withdraw/dashboard'
import WithdrawRecords from '../components/withdraw/record-index'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: 'withdraw/dashboard', component: WithdrawDashboard, name: 'WithdrawDashboard'},
            {path: 'withdraw/records', component: WithdrawRecords, name: 'WithdrawRecords'},
        ]
    }
]