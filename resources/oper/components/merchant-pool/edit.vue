<template>
    <page title="编辑商户信息" :breadcrumbs="{'商户池': '/merchant/pool'}">
        <merchant-pool-form
                v-loading="isLoading"
                v-if="merchant"
                :data="merchant"
                @cancel="cancel"
                @save="doEdit"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantPoolForm from './merchant-pool-form'
    export default {
        name: "merchant-pool-edit",
        components: {
            MerchantPoolForm,
        },
        data() {
            return {
                isLoading: false,
                id: null,
                merchant: null,
            }
        },
        methods: {
            doEdit(data){
                this.isLoading = true;
                api.post('/merchant/pool/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/merchant/pool');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            getDetail(){
                this.isLoading = true;
                api.get('/merchant/pool/detail', {id: this.id}).then(data => {
                    this.merchant = data;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/merchant/pool');
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/merchant/pool');
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>