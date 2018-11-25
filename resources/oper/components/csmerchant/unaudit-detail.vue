<template>
    <page title="超市商户详情 ">
        <merchant-audit-detail v-if="merchant" :data="merchant" />
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantAuditDetail from './merchant-audit-detail'
    export default {
        name: "detail",
        data() {
            return {
                id: null,
                merchant: null,
            }
        },
        methods: {
            getDetail(){
                api.get('cs/merchant/audit/detail', {id: this.id,}).then(data => {
                    this.merchant = data;
                });
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                return false;
            }
            this.getDetail();
        },
        components: {
            MerchantAuditDetail
        }
    }
</script>

<style scoped>

</style>