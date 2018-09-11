import Home from '../components/home'
// 账户管理

import WalletMerchantList from '../components/wallet/merchant/list'
import WalletMerchantBillRecord from '../components/wallet/merchant/bill-record'

import WalletUserList from '../components/wallet/user/list'
import WalletUserBillRecord from '../components/wallet/user/bill-record'

import WalletOperList from '../components/wallet/oper/list'
import WalletOperBillRecord from '../components/wallet/oper/bill-record'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/merchant', component: WalletMerchantList, name: 'WalletMerchantList'},
            {path: '/wallet/merchant/bill', component: WalletMerchantBillRecord, name: 'WalletMerchantBillRecord'},

            {path: '/wallet/user', component: WalletUserList, name: 'WalletUserList'},
            {path: '/wallet/user/bill', component: WalletUserBillRecord, name: 'WalletUserBillRecord'},

            {path: '/wallet/oper', component: WalletOperList, name: 'WalletOperList'},
            {path: '/wallet/oper/bill', component: WalletOperBillRecord, name: 'WalletOperBillRecord'},
        ]
    }
]