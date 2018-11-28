import Home from '../components/home'
// 账户管理

import WalletMerchantList from '../components/wallet/merchant/list'
import WalletMerchantBillRecord from '../components/wallet/merchant/bill-record'

import CsWalletMerchantList from '../components/wallet/cs_merchant/list'
import CsWalletMerchantBillRecord from '../components/wallet/cs_merchant/bill-record'

import WalletUserList from '../components/wallet/user/list'
import WalletUserBillRecord from '../components/wallet/user/bill-record'

import WalletOperList from '../components/wallet/oper/list'
import WalletOperBillRecord from '../components/wallet/oper/bill-record'

import WalletBizerList from '../components/wallet/bizer/list'
import WalletBizerBillRecord from '../components/wallet/bizer/bill-record'

// 分润管理
import FeeSplittingList from '../components/fee_splitting/list'

export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/wallet/merchant', component: WalletMerchantList, name: 'WalletMerchantList'},
            {path: '/wallet/merchant/bill', component: WalletMerchantBillRecord, name: 'WalletMerchantBillRecord'},

            {path: '/wallet/cs_merchant', component: CsWalletMerchantList, name: 'CsWalletMerchantList'},
            {path: '/wallet/cs_merchant/bill', component: CsWalletMerchantBillRecord, name: 'CsWalletMerchantBillRecord'},

            {path: '/wallet/user', component: WalletUserList, name: 'WalletUserList'},
            {path: '/wallet/user/bill', component: WalletUserBillRecord, name: 'WalletUserBillRecord'},

            {path: '/wallet/oper', component: WalletOperList, name: 'WalletOperList'},
            {path: '/wallet/oper/bill', component: WalletOperBillRecord, name: 'WalletOperBillRecord'},

            {path: '/wallet/bizer', component: WalletBizerList, name: 'WalletBizerList'},
            {path: '/wallet/bizer/bill', component: WalletBizerBillRecord, name: 'WalletBizerBillRecord'},


            {path: '/fee_splitting/list', component: FeeSplittingList, name: 'FeeSplittingList'},
        ]
    }
]