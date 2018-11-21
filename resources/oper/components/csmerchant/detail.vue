<template>
    <page title="超市商户详情 ">
        <merchant-detail v-if="merchant" :data="merchant" />
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantDetail from './merchant-detail'
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
                api.get('cs/merchant/detail', {id: this.id,}).then(data => {
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