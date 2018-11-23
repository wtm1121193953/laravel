import Home from '../components/home'

/*系统设置*/
import MiniMerchantShareSetting from '../components/setting/mini-merhant-share'
import CreditRules from '../components/setting/credit-rules'
import PageSetting from '../components/setting/page-setting'
import PageSettingForm from '../components/setting/page-setting-form'
import FilterKeywordList from '../components/setting/filter-keyword/list'
import BanksList from '../components/bank/list'
import MerchantElectronicContract from '../components/setting/merchant-electronic-contract'
import NavigationList from '../components/navigation/list'

export default [
    // 系统设置模块
    {
        path: '/',
        component: Home,
        children: [
            {path: 'setting/mini_merchant_share', component: MiniMerchantShareSetting, name: 'MiniMerchantShareSetting'},
            {path: 'setting/credit_rules', component: CreditRules, name: 'CreditRules'},
            {path: 'setting/page_setting', component: PageSetting, name: 'PageSetting'},
            {path: 'setting/page_setting_form', component: PageSettingForm, name: 'PageSettingForm'},
            {path: 'setting/filter_keyword_list', component: FilterKeywordList, name: 'FilterKeywordList'},
            {path: 'bank/list', component: BanksList, name: 'BanksList'},
            {path: 'setting/merchant_electronic_contract', component: MerchantElectronicContract, name: 'MerchantElectronicContract'},
            {path: 'setting/navigations', component: NavigationList, name: 'NavigationList'},
        ]
    },
]