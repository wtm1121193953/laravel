<template>
    <page title="商户详情 ">
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
                api.get('merchant/detail', {id: this.id,}).then(data => {
                    this.merchant = data;

                    this.merchant.business_time = JSON.parse(data.business_time);
                    this.merchant.desc_pic_list = data.desc_pic_list ? data.desc_pic_list.split(',') : [];
                    this.merchant.contract_pic_url = data.contract_pic_url ? data.contract_pic_url.split(',') : [];
                    this.merchant.other_card_pic_urls = data.other_card_pic_urls ? data.other_card_pic_urls.split(',') : [];
                    this.merchant.bank_card_pic_a = data.bank_card_pic_a ? data.bank_card_pic_a.split(',') : [];
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