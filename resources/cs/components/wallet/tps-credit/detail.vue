<template>
    <page title="积分明细" v-loading="formLoading" :breadcrumbs="{我的TPS积分: '/wallet/credit/list'}">
        <el-row :gutter="20">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <el-form-item label="交易号">
                        {{data.consume_quota_no}}
                    </el-form-item>
                    <el-form-item label="交易时间">
                        {{data.created_at}}
                    </el-form-item>
                    <el-form-item label="贡献TPS积分">
                        {{parseFloat(data.sync_tps_credit).toFixed(2)}}
                    </el-form-item>
                    <el-form-item label="置换时间">
                        {{data.sync_time}}
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="交易类型">
                        <span>被分享人贡献</span>
                    </el-form-item>
                    <el-form-item label="用户手机号码">
                        {{data.consume_user_mobile}}
                    </el-form-item>
                    <el-form-item label="TPS积分状态">
                        <span v-if="data.status == 1">冻结中</span>
                        <span v-else-if="data.status == 2">已解冻待置换</span>
                        <span v-else-if="data.status == 3">已置换TPS</span>
                        <span v-else-if="data.status == 4">已退款</span>
                        <span v-else>未知({{data.status}})</span>
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
                formLoading: false,
            }
        },
        methods: {
            getDetail(id) {
                this.formLoading = true;
                api.get('/wallet/tpsCredit/detail', {id: id}).then(data => {
                    this.data = data;
                    this.formLoading = false;
                })
            },
            goBack() {
                router.push('/wallet/credit/list');
            }
        },
        created() {
            let id = this.$route.query.id;
            if(!id){
                this.$message.error('id不能为空');
                router.push('/wallet/credit/list');
            }
            this.getDetail(id);
        }
    }
</script>

<style scoped>

</style>