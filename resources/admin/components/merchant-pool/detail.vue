<template>
    <page title="商户详情" :breadcrumbs="{商户池: '/merchant/pool'}">
        <merchant-detail v-if="merchant" type="poolOnly" :data="merchant" @change="merchantChange"/>
    </page>
</template>

<script>
    import MerchantDetail from '../merchant/merchant-detail'
    export default {
        name: "detail",
        data() {
            return {
                id: null,
                merchant: null,
            }
        },
        methods: {
            merchantChange(){
                router.push({
                    path: '/merchant/pool'
                });
            },
            getDetail(){
                api.get('merchant/pool/detail', {id: this.id}).then(data => {
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
            MerchantDetail
        }
    }
</script>

<style scoped>

</style>