import Home from '../components/home'
// 提现管理
// 提现汇总
import WithdrawDashboard from '../components/withdraw/dashboard'

// 提现记录
import WithdrawRecords from '../components/withdraw/record/index'

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