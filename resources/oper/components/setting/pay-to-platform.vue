<template>
    <page title="支付到平台">
        <el-form label-position="left">
            <el-form-item label="运营中心下的商家是否支付到平台" label-width="250px">
                <el-switch
                    v-model="payToPlatform"
                    active-text="支付到平台"
                    inactive-text="支付到运营中心"
                    :active-value="1"
                    :inactive-value="0"
                    :disabled="payToPlatform == 1"
                    @change="change"
                ></el-switch>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "pay-to-platform",
        data() {
            return {
                payToPlatform: 0,
            }
        },
        methods: {
            change() {
                api.post('/setting/setPayToPlatform').then(data => {
                    this.$message.success('修改成功');
                })
            },
            getPayToPlatformStatus() {
                api.get('/setting/payToPlatform').then(data => {
                    this.payToPlatform = parseInt(data.pay_to_platform);
                })
            }
        },
        created() {
            this.getPayToPlatformStatus();
        }
    }
</script>

<style scoped>

</style>