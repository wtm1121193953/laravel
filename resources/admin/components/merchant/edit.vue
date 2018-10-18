<template>
    <page title="编辑商户信息" :breadcrumbs="{'商户列表': '/merchants'}">
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
                api.post('merchant/edit', data).then(() => {
                    this.$message.success('保存成功');
                    router.push({
                        path: '/merchants',
                        name: 'MerchantList',
                        params: this.$route.params,
                    });
                    store.commit('setCurrentMenu', '/merchants');
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
                router.push('/merchants');
            },
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