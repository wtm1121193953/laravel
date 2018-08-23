import Home from '../components/home'
import WalletSummaryList from '../components/wallet/summary/list'
import WalletSummaryDetail from '../components/wallet/summary/detail'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/summary/list', component: WalletSummaryList, name: 'WalletSummaryList'},
            {path: '/wallet/summary/detail', component: WalletSummaryDetail, name: 'WalletSummaryDetail'},
        ]
    }
];
