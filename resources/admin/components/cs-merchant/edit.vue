<template>
    <page title="编辑超市商户信息" :breadcrumbs="{'超市商户管理': '/CsMerchant/unaudits'}">
        <merchant-form
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
    import MerchantForm from './merchant-form'
    export default {
        name: "merchant-edit",
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
                api.post('/CsMerchant/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push({
                        path: '/CsMerchant/unaudits',
                        name: 'CsMerchantUnauditList',
                        params: this.$route.params,
                    });
                    store.commit('setCurrentMenu', '/csMerchant/unaudits');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            getDetail(){
                this.isLoading = true;
                api.get('/CsMerchant/detail' , {id: this.id}).then(data => {
                    this.merchant = data;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            cancel(){
                router.push('/CsMerchant/unaudits');
            },
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/CsMerchant/unaudits');
                return false;
            }
            this.getDetail();
        }
    }
</script>

<style scoped>

</style>