<template>
    <page title="添加商户" :breadcrumbs="{'我的商户': '/merchants'}">
        <merchant-form v-loading="isLoading" @cancel="cancel" @save="doAdd" @saveDraft="addDraft"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from './merchant-form'

    export default {
        name: "merchant-add",
        components: {
            MerchantForm,
        },
        data() {
            return {
                isLoading: false,
            }
        },
        methods: {
            doAdd(data){
                this.isLoading = true;
                api.post('/merchant/add', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/merchants');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/merchants');
            },
            addDraft(data) {
                api.post('/merchant/draft/add', data).then(() => {
                    this.$message.success('保存成功');
                    router.push('/merchants');
                }).finally(() => {
                    this.isLoading = false;
                })
            }
        }
    }
</script>

<style scoped>

</style>