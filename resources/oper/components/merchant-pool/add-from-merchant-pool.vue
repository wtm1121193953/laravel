<template>
    <page title="激活商户" :breadcrumbs="{'商户池': '/merchant/pool'}">
        <merchant-form
                v-loading="isLoading"
                v-if="merchant"
                :data="merchant"
                pool-info-readonly
                @cancel="cancel"
                @save="doEdit"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from '../merchant/merchant-form'
    export default {
        name: "add-from-merchant-pool",
        components: {
            MerchantForm,
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
                api.post('/merchant/addFromMerchantPool', data).then(() => {
                    this.$message.success('提交成功');
                    this.$menu.change('/merchants');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            getDetail(){
                this.isLoading = true;
                api.get('/merchant/detail', {id: this.id}).then(data => {
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