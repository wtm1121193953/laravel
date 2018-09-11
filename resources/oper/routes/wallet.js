import Home from '../components/home'
import WalletSummaryList from '../components/wallet/summary/list'
import WalletSummaryDetail from '../components/wallet/summary/detail'

import WalletConsumeList from '../components/wallet/my-consume-quota/list'
import WalletConsumeDetail from '../components/wallet/my-consume-quota/detail'

import WalletWithdrawPassword from '../components/wallet/withdraw/set-password'
import WalletWithdrawForm from '../components/wallet/withdraw/form'
import WalletWithdrawSuccess from '../components/wallet/withdraw/success'

import WalletTpsCreditList from '../components/wallet/tps-credit/list'
import WalletTpsCreditDetail from '../components/wallet/tps-credit/detail'

export default [
    {
        path: '/',
        component: Home,
        children: [
            // 账户总览
            {path: '/wallet/summary/list', component: WalletSummaryList, name: 'WalletSummaryList'},
            {path: '/wallet/summary/detail', component: WalletSummaryDetail, name: 'WalletSummaryDetail'},

            // 我的消费额
            {path: '/wallet/consume/list', component: WalletConsumeList, name: 'WalletConsumeList'},
            {path: '/wallet/consume/detail', component: WalletConsumeDetail, name: 'WalletConsumeDetail'},

            // 我要提现 和 密码管理
            {path: '/wallet/withdraw/password', component: WalletWithdrawPassword, name: 'WalletWithdrawPassword'},
            {path: '/wallet/withdraw/form', component: WalletWithdrawForm, name: 'WalletWithdrawForm'},
            {path: '/wallet/withdraw/success', component: WalletWithdrawSuccess, name: 'WalletWithdrawSuccess'},

            // 我的TPS积分
            {path: '/wallet/credit/list', component: WalletTpsCreditList, name: 'WalletTpsCreditList'},
            {path: '/wallet/credit/detail', component: WalletTpsCreditDetail, name: 'WalletTpsCreditDetail'},
        ]
    }
];
