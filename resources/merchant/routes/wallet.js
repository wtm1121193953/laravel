import Home from '../components/home'
import WalletSummaryList from '../components/wallet/summary/list'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/summary/list', component: WalletSummaryList, name: 'WalletSummaryList'},
        ]
    }
];
