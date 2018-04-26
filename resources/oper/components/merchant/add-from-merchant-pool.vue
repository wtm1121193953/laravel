<template>
    <page title="编辑商户信息" :breadcrumbs="{'我的商户': '/merchants'}">
        <merchant-form
                v-loading="isLoading"
                v-if="merchant"
                :data="merchant"
                @cancel="cancel"
                @save="doEdit"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from './merchant-form'
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
                    this.isLoading = false;
                    this.$message.success('保存成功');
                    router.push('/merchants');
                })
            },
            getDetail(){
                this.isLoading = true;
                api.get('/merchant/detail', {id: this.id}).then(data => {
                    this.isLoading = false;
                    this.merchant = data;
                })
            },
            cancel(){
                router.push('/merchants');
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/merchants');
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>