import WalletList from '../components/wallet/list'
import WithdrawPasswordForm from '../components/withdraw/password-form'

export default [
    {path: '/wallet/bills', component: WalletList, name: 'WalletList'},

    {path: '/withdraw/password/form', component: WithdrawPasswordForm, name: 'WithdrawPasswordForm'},
];