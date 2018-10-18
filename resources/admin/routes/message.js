
import Home from '../components/home'
import SystemsList from '../components/message/systems.vue'

/**
 * category 模块
 */
export default [
    {
        path: '/',
        component: Home,
        children: [
            {path: '/message/systems', component: SystemsList, name: 'SystemsList'},
            // {path: '/message/systems', component: SystemsList, name: 'SystemsList'},
        ]
    },
];