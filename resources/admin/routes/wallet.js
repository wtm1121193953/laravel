import Home from '../components/home'
// 账户管理

import WalletMerchantList from '../components/wallet/merchant/list'
import WalletMerchantBillRecord from '../components/wallet/merchant/bill-record'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/merchant', component: WalletMerchantList, name: 'WalletMerchantList'},
            {path: '/wallet/merchant/bill', component: WalletMerchantBillRecord, name: 'WalletMerchantBillRecord'},
        ]
    }
]