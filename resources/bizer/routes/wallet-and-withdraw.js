import WalletList from '../components/wallet/list'
import WithdrawPasswordForm from '../components/withdraw/password-form'
import WithdrawPasswordSuccess from '../components/withdraw/password-success'

export default [
    {path: '/wallet/bills', component: WalletList, name: 'WalletList'},

    {path: '/withdraw/password/form', component: WithdrawPasswordForm, name: 'WithdrawPasswordForm'},
    {path: '/withdraw/password/success', component: WithdrawPasswordSuccess, name: 'WithdrawPasswordSuccess'},
];