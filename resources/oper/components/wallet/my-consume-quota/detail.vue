<template>
    <page title="贡献值明细" :breadcrumbs="{我的贡献值: '/wallet/consume/list'}">
        <el-row :gutter="20">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <el-form-item label="交易号">
                        {{data.consume_quota_no}}
                    </el-form-item>
                    <el-form-item label="交易时间">
                        {{data.created_at}}
                    </el-form-item>
                    <el-form-item label="交易金额">
                        {{data.pay_price}}元
                    </el-form-item>
                    <!--<el-form-item label="消费额状态">
                        <span v-if="data.status == 1">冻结中</span>
                        <span v-else-if="data.status == 2">已解冻待置换</span>
                        <span v-else-if="data.status == 3">已置换TPS</span>
                        <span v-else-if="data.status == 4">已退款</span>
                        <span v-else>未知({{data.status}})</span>
                    </el-form-item>-->
                    <el-form-item label="原订单编号">
                        {{data.order_no}}
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="交易类型">
                        <span>下级贡献</span>
                    </el-form-item>
                    <el-form-item label="用户手机号码">
                        {{data.consume_user_mobile}}
                    </el-form-item>
                    <el-form-item label="获得贡献值">
                        {{data.consume_quota}}
                    </el-form-item>
                    <el-form-item v-if="data.status == 1" label="解冻时间">
                        {{data.time}}
                    </el-form-item>
                    <el-form-item v-if="data.status == 3" label="置换时间">
                        {{data.time}}
                    </el-form-item>
                    <el-form-item v-if="data.status == 4" label="退款时间">
                        {{data.time}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-row>
        <el-button type="primary" @click="goBack">返 回</el-button>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-consume-detail",
        data() {
            return {
                data: {},
            }
        },
        methods: {
            getDetail(id) {
                api.get('/wallet/consume/detail', {id: id}).then(data => {
                    this.data = data;
                })
            },
            goBack() {
                router.push('/wallet/consume/list');
            }
        },
        created() {
            let id = this.$route.query.id;
            if(!id){
                this.$message.error('id不能为空');
                router.push('/wallet/consume/list');
            }
            this.getDetail(id);
        }
    }
</script>

<style scoped>

</style>