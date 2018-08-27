import Home from '../components/home'
// 提现管理
// 提现汇总
import WithdrawDashboard from '../components/withdraw/dashboard'

// 提现记录
import WithdrawRecords from '../components/withdraw/record/index'
import WithdrawRecordMerchantDetailAudit from '../components/withdraw/record/merchant-detail-audit'
import WithdrawRecordOperDetailAudit from '../components/withdraw/record/oper-detail-audit'
import WithdrawRecordUserDetailAudit from '../components/withdraw/record/user-detail-audit'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/withdraw/dashboard', component: WithdrawDashboard, name: 'WithdrawDashboard'},

            {path: '/withdraw/records', component: WithdrawRecords, name: 'WithdrawRecords'},
            {path: '/withdraw/record/merchant', component: WithdrawRecordMerchantDetailAudit, name: 'WithdrawRecordMerchantDetailAudit'},
            {path: '/withdraw/record/oper', component: WithdrawRecordOperDetailAudit, name: 'WithdrawRecordOperDetailAudit'},
            {path: '/withdraw/record/user', component: WithdrawRecordUserDetailAudit, name: 'WithdrawRecordUserDetailAudit'},
        ]
    }
]