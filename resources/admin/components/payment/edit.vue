<template>
    <page title="修改支付方式" :breadcrumbs="{支付方式管理: '/payments'}" v-loading="loading">
        <el-col :span="12">
            <payment-form
                    v-if="payment"
                    :data="payment"
                    ref="addForm"
                    @cancel="cancel"
                    @save="doEdit"/>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import PaymentForm from "./payment-form";

    export default {
        name: "add",
        data(){
            return {
                loading: false,
                payment: null,
                id: null,
            }
        },
        methods: {
            cancel(){
                router.push('/payments');
            },
            getDetail(){
                this.loading = true;
                api.get('/payment/detail', {id: this.id}).then(data => {
                    this.payment = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            doEdit(data){
                this.loading = true;
                api.post('/payment/edit', data).then((data) => {
                    router.push('/payments');
                }).finally(() => {
                    this.loading = false;
                })
            },
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/payments');
                return false;
            }
            this.getDetail();
        },
        components: {
            PaymentForm
        }
    }
</script>

<style scoped>

</style>