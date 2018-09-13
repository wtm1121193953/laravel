<template>
    <page title="编辑试点商户信息" :breadcrumbs="{'试点商户': '/merchant/pilots'}">
        <pilot-merchant-form
                v-loading="isLoading"
                v-if="merchant"
                :data="merchant"
                @cancel="cancel"
                @save="doEdit"
        />
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import PilotMerchantForm from './form'
    export default {
        name: "pilot-merchant-edit",
        components: {
            PilotMerchantForm,
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
                api.post('/merchant/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push({
                        path: '/merchant/pilots',
                        name: 'MerchantPilotList',
                        params: this.$route.params,
                    });
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            getDetail(){
                this.isLoading = true;
                api.get('/merchant/detail' , {id: this.id}).then(data => {
                    this.merchant = data;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/merchant/pilots');
            },
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/merchant/pilots');
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>