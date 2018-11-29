<template>
    <page title="订单明细" :breadcrumbs="{账户总览: '/wallet/summary/list'}">
        <el-row :gutter="20" v-if="billData.type == 2 || billData.type == 4">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <el-form-item label="订单类型">
                        <span v-if="billData.type == 2">被分享人消费奖励</span>
                        <span v-else-if="billData.type == 4">被分享人消费奖励退款</span>
                        <span v-else>未知 ({{billData.type}})</span>
                    </el-form-item>
                    <el-form-item label="入账金额">
                        <span v-if="billData.inout_type == 1">+</span>
                        <span v-else>-</span>
                        {{billData.amount}}元
                    </el-form-item>
                    <el-form-item label="交易时间">
                        {{billData.created_at}}
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="交易编号">
                        {{billData.bill_no}}
                    </el-form-item>
                    <el-form-item label="余额">
                        {{billData.after_amount}}元
                    </el-form-item>
                    <el-form-item label="商户名称">
                        {{billData.merchant_name}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-row>
        <el-row :gutter="20" v-if="billData.type == 2 || billData.type == 4">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <el-form-item label="交易用户">
                        {{orderOrWithdrawData.notify_mobile}}
                    </el-form-item>
                    <el-form-item label="订单编号">
                        {{orderOrWithdrawData.order_no}}
                    </el-form-item>
                    <el-form-item label="订单状态">
                        <span v-if="parseInt(orderOrWithdrawData.status) === 1">未支付</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 2">已取消</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 3">已关闭[超时自动关闭]</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 4">已支付</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 5">退款中[保留状态]</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 6">已退款</span>
                        <span v-else-if="parseInt(orderOrWithdrawData.status) === 7">已完成[不可退款]</span>
                        <span v-else>未知 ({{orderOrWithdrawData.status}})</span>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="订单类型">
                        <span v-if="orderOrWithdrawData.type == 1">团购</span>
                        <span v-else-if="orderOrWithdrawData.type == 2">买单</span>
                        <span v-else-if="orderOrWithdrawData.type == 3">单品</span>
                        <span v-else-if="orderOrWithdrawData.type == 4">超市</span>
                        <span v-else>未知({{orderOrWithdrawData.type}})</span>
                    </el-form-item>
                    <el-form-item label="订单金额">
                        {{orderOrWithdrawData.pay_price}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-row>
        <el-row :gutter="20" v-if="billData.type == 7 || billData.type == 8">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <el-form-item label="订单类型">
                        <span v-if="billData.type == 7">
                        提现
                        <span v-if="orderOrWithdrawData.status == 1">(审核中)</span>
                        <span v-else-if="orderOrWithdrawData.status == 2">(审核通过)</span>
                        <span v-else-if="orderOrWithdrawData.status == 3">(已打款)</span>
                        <span v-else-if="orderOrWithdrawData.status == 4">(打款失败)</span>
                        <span v-else-if="orderOrWithdrawData.status == 5">(审核不通过)</span>
                    </span>
                        <span v-else-if="billData.type == 8">提现失败</span>
                        <span v-else>未知 ({{billData.type}})</span>
                    </el-form-item>
                    <el-form-item label="提现金额">
                        {{orderOrWithdrawData.amount}}元
                    </el-form-item>
                    <el-form-item label="到账金额">
                        {{orderOrWithdrawData.remit_amount}}元
                    </el-form-item>
                    <el-form-item label="提现账户名">
                        {{orderOrWithdrawData.bank_card_open_name}}
                    </el-form-item>
                    <el-form-item label="开户行">
                        {{orderOrWithdrawData.bank_name}}
                    </el-form-item>
                    <el-form-item label="提现状态">
                        <span v-if="orderOrWithdrawData.status == 1">审核中</span>
                        <span v-else-if="orderOrWithdrawData.status == 2">审核通过</span>
                        <span v-else-if="orderOrWithdrawData.status == 3">已打款</span>
                        <span v-else-if="orderOrWithdrawData.status == 4">打款失败</span>
                        <span v-else-if="orderOrWithdrawData.status == 5">审核不通过</span>
                        <span v-else>未知 ({{orderOrWithdrawData.status}})</span>
                    </el-form-item>
                    <el-form-item label="发票快递公司">
                        {{orderOrWithdrawData.invoice_express_company}}
                    </el-form-item>
                    <el-form-item label="提现编号">
                        {{orderOrWithdrawData.withdraw_no}}
                    </el-form-item>
                    <el-form-item label="备注">
                        {{orderOrWithdrawData.remark}}
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="交易编号">
                        {{billData.bill_no}}
                    </el-form-item>
                    <el-form-item label="手续费">
                        {{orderOrWithdrawData.charge_amount}}
                    </el-form-item>
                    <el-form-item label="余额">
                        {{billData.after_amount}}
                    </el-form-item>
                    <el-form-item label="提现银行卡">
                        {{orderOrWithdrawData.bank_card_no}}
                    </el-form-item>
                    <el-form-item label="提现发起时间">
                        {{orderOrWithdrawData.created_at}}
                    </el-form-item>
                    <el-form-item label="商户名称">
                        {{billData.merchant_name}}
                    </el-form-item>
                    <el-form-item label="快递编号">
                        {{orderOrWithdrawData.invoice_express_no}}
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
        name: "wallet-summary-detail",
        data() {
            return {
                billData: {},
                orderOrWithdrawData: {},
            }
        },
        methods: {
            getDetail(id) {
                api.get('/wallet/bill/detail', {id: id}).then(data => {
                    this.billData = data.billData;
                    this.orderOrWithdrawData = data.orderOrWithdrawData;
                })
            },
            goBack() {
                router.push('/wallet/summary/list');
            }
        },
        created() {
            let id = this.$route.query.id;
            if(!id){
                this.$message.error('id不能为空');
                router.push('/wallet/summary/list');
            }
            this.getDetail(id);
        }
    }
</script>

<style scoped>

</style>