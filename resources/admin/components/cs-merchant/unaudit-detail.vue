<template>
    <page title="商户详情" :breadcrumbs="{商户审核管理: '/merchants'}">
        <merchant-detail v-if="merchant" :data="merchant" :auditType="auditType" @change="merchantChange"/>
    </page>
</template>

<script>
    import MerchantDetail from './merchant-detail'
    export default {
        name: "detail",
        data() {
            return {
                id: null,
                merchant: null,
                auditType:null
            }
        },
        methods: {
            merchantChange(){
                if (this.$route.query.isAudit){
                    router.push({
                        path: '/cs/merchant/unaudits',
                        name: 'MerchantUnauditList',
                        params: this.$route.params,
                    });
                } else {
                    router.push({
                        path: '/merchants',
                        name: 'MerchantList',
                        params: this.$route.params,
                    });
                }
            },
            getDetail(){
                api.get('cs/merchant/audit/detail', {id: this.id,}).then(data => {
                    this.merchant = data;
                });
            }
        },
        created(){
            this.id = this.$route.query.id;
            this.auditType = parseInt(this.$route.query.auditType);
            if(!this.id){
                this.$message.error('id不能为空');
                return false;
            }
            this.getDetail();
        },
        components: {
            MerchantDetail
        }
    }
</script>

<style scoped>

</style>