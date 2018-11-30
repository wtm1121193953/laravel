import Home from '../components/home'
// 提现管理
// 提现汇总
import WithdrawDashboard from '../components/withdraw/dashboard'

// 提现记录
import WithdrawRecords from '../components/withdraw/record/index'
import WithdrawRecordMerchantDetailAudit from '../components/withdraw/record/merchant-detail-audit'
import WithdrawRecordOperDetailAudit from '../components/withdraw/record/oper-detail-audit'
import WithdrawRecordUserDetailAudit from '../components/withdraw/record/user-detail-audit'
import WithdrawRecordBizerDetailAudit from '../components/withdraw/record/bizer-detail-audit'
import WithdrawRecordCsDetailAudit from '../components/withdraw/record/cs-merchant-detail-audit'

// 提现批次管理
import WithdrawBatch from '../components/withdraw/batch/list'
import WithdrawBatchDetail from '../components/withdraw/batch/detail'

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
            {path: '/withdraw/record/bizer', component: WithdrawRecordBizerDetailAudit, name: 'WithdrawRecordBizerDetailAudit'},
            {path: '/withdraw/record/cs', component: WithdrawRecordCsDetailAudit, name: 'WithdrawRecordCsDetailAudit'},

            {path: '/withdraw/batch', component: WithdrawBatch, name: 'WithdrawBatch'},
            {path: '/withdraw/batch/detail', component: WithdrawBatchDetail, name: 'WithdrawBatchDetail'},
        ]
    }
]