<template>
    <page title="添加试点商户" :breadcrumbs="{'试点商户': '/merchant/pilots'}">
        <pilot-merchant-form ref="form" v-loading="isLoading" @cancel="cancel" @save="doAdd"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import PilotMerchantForm from './form'

    export default {
        name: "pilot-merchant-add",
        components: {
            PilotMerchantForm,
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
                    this.$refs.form.resetForm();
                    router.push('/merchant/pilots');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/merchant/pilots');
            },
        },
        created() {

        }
    }
</script>

<style scoped>

</style>