<template>
    <page title="录入商户" :breadcrumbs="{'商户池': '/merchant/pool'}">
        <merchant-pool-form v-loading="isLoading" @cancel="cancel" @save="doAdd"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantPoolForm from '../merchant/merchant-pool-info-form'
    export default {
        name: "merchant-pool-add",
        components: {
            MerchantPoolForm,
        },
        data() {
            return {
                isLoading: false,
            }
        },
        methods: {
            doAdd(data){
                this.isLoading = true;
                api.post('/merchant/pool/add', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/merchant/pool');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/merchant/pool');
            },
        }
    }
</script>

<style scoped>

</style>