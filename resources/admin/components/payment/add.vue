<template>
    <page title="添加支付方式" :breadcrumbs="{支付方式管理: '/versions'}" v-loading="loading">
        <el-col :span="12">
            <payment-form
                    ref="addForm"
                    @cancel="cancel"
                    @save="doAdd"/>
        </el-col>
    </page>
</template>

<script>
    import PaymentForm from './payment-form'
    import api from '../../../assets/js/api'

    export default {
        name: "add",
        data(){
            return {
                loading: false,
                version: null,
                id: null,
            }
        },
        methods: {
            cancel(){
                router.push('/payments');
            },
            doAdd(data){
                this.loading = true;
                api.post('/payment/add', data).then(() => {
                    this.$refs.addForm.resetForm();
                    router.push('/payments');
                }).finally(() => {
                    this.loading = false;
                })
            }
        },
        created(){
        },
        components: {
            PaymentForm
        }
    }
</script>

<style scoped>

</style>