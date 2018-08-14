<template>
    <page title="订单管理" v-loading="isLoading">
        
    </page>
</template>

<script>
    // import api from '../../../assets/js/api'
    // import OrderForm from './form'

    export default {
        data() {
            return {
                isLoading: false,
                isShow: false,
                isShowItems: false,
                list: [],
                query: {
                    page: 1,
                    orderNo: '',
                    notifyMobile: '',
                    createdAt: '',
                    type: '',
                    status: '',
                    goodsName: '',
                },
                total: 0,
                order: {},
                verify_code: '',
                verify_success: false,
                verify_fail: false,
            }
        },
        methods: {
            
        },
        created() {
            
        },
        components: {
            // OrderForm,
        }
    }
</script>

<style scoped>

</style>