import Home from '../components/home'
import PaymentList from '../components/payment/list'
import PaymentAdd from '../components/payment/add'
import PaymentEdit from '../components/payment/edit'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/payments', component: PaymentList, name: 'PaymentList'},
            {path: '/payment/add', component: PaymentAdd, name: 'PaymentAdd'},
            {path: '/payment/edit', component: PaymentEdit, name: 'PaymentEdit'},
        ]
    },
];