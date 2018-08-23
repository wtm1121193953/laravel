import Home from '../components/home'
import WalletSummaryList from '../components/wallet/summary/list'
import WalletSummaryDetail from '../components/wallet/summary/detail'

import WalletConsumeList from '../components/wallet/my-consume-quota/list'
import WalletConsumeDetail from '../components/wallet/my-consume-quota/detail'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/summary/list', component: WalletSummaryList, name: 'WalletSummaryList'},
            {path: '/wallet/summary/detail', component: WalletSummaryDetail, name: 'WalletSummaryDetail'},

            {path: '/wallet/consume/list', component: WalletConsumeList, name: 'WalletConsumeList'},
            {path: '/wallet/consume/detail', component: WalletConsumeDetail, name: 'WalletConsumeDetail'},
        ]
    }
];
