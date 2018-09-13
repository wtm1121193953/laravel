<template>
    <page title="试点商户详情" :breadcrumbs="{试点商户列表: '/merchant/pilots'}">
        <merchant-detail v-if="merchant" :data="merchant" :auditType="auditType" @change="merchantChange"/>
    </page>
</template>

<script>
    import MerchantDetail from './merchant-pilot-detail'
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
                router.push({
                    path: '/merchant/pilots',
                    name: 'MerchantPilotList',
                    params: this.$route.params,
                });
            },
            getDetail(){
                api.get('merchant/detail', {id: this.id,}).then(data => {
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