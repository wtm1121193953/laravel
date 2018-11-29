<template>
    <el-row :gutter="20">
        <el-col v-if="billData.type == 9 || billData.type == 10">
            <el-form label-width="120px" label-position="left" size="small"  v-if="billData.type == 9 || billData.type == 10">
                <el-col :span="12">
                    <el-form-item label="订单类型">
                        <span v-if="billData.type == 9">交易分润入账</span>
                        <span v-else-if="billData.type == 10">交易分润退款</span>
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
                    <!--<el-form-item label="解冻时间">
                        {{billData.balance_unfreeze_time}}
                    </el-form-item>-->
                </el-col>
                <el-col :span="12">
                    <el-form-item label="交易编号">
                        {{billData.bill_no}}
                    </el-form-item>
                    <el-form-item label="余额">
                        {{billData.after_amount}}元
                    </el-form-item>
                    <el-form-item label="运营中心名称">
                        {{billData.oper_name}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-col>
        <el-col :gutter="20" v-if="billData.type == 9 || billData.type == 10">
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
        </el-col>
        <el-col :gutter="20" v-if="billData.type == 7 || billData.type == 8">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col :span="12">
                    <!--<el-form-item label="订单类型">
                        <span v-if="billData.type == 7">
                        提现
                        <span v-if="orderOrWithdrawData.status == 1">[审核中]</span>
                        <span v-else-if="orderOrWithdrawData.status == 2">[审核通过]</span>
                        <span v-else-if="orderOrWithdrawData.status == 3">[已打款]</span>
                        <span v-else-if="orderOrWithdrawData.status == 4">[打款失败]</span>
                        <span v-else-if="orderOrWithdrawData.status == 5">[审核不通过]</span>
                        <span v-else>[未知 ({{orderOrWithdrawData.status}})]</span>
                    </span>
                        <span v-else-if="billData.type == 8">提现失败</span>
                        <span v-else>未知 ({{billData.type}})</span>
                    </el-form-item>-->
                    <el-form-item label="交易编号">
                        {{billData.bill_no}}
                    </el-form-item>
                    <el-form-item label="提现金额">
                        {{orderOrWithdrawData.amount}}元
                    </el-form-item>
                    <el-form-item label="到账金额">
                        {{orderOrWithdrawData.remit_amount}}元
                    </el-form-item>
                    <el-form-item label="手续费">
                        {{orderOrWithdrawData.charge_amount}}
                    </el-form-item>
                    <!--<el-form-item label="运营中心名称">
                        {{billData.oper_name}}
                    </el-form-item>-->
                </el-col>
                <el-col :span="12">
                    <el-form-item label="提现发起时间">
                        {{orderOrWithdrawData.created_at}}
                    </el-form-item>
                    <!--<el-form-item label="余额">
                        {{billData.after_amount}}
                    </el-form-item>-->
                    <el-form-item label="提现账户名">
                        {{orderOrWithdrawData.bank_card_open_name}}
                    </el-form-item>
                    <el-form-item label="提现银行卡">
                        {{orderOrWithdrawData.bank_card_no}}
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
                    <!--<el-form-item label="提现编号">
                        {{orderOrWithdrawData.withdraw_no}}
                    </el-form-item>-->
                    <el-form-item label="备注">
                        {{orderOrWithdrawData.remark}}
                    </el-form-item>
                </el-col>
                <el-col class="tips">注意：具体到账时间请以银行通知时间为准</el-col>
            </el-form>
        </el-col>
    </el-row>
</template>

<script>
    export default {
        name: "wallet-bill-detail",
        props: {
            billData: {
                type: Object,
            },
            orderOrWithdrawData: {
                type: Object,
            },
        },
        data() {
            return {

            }
        },
    }
</script>

<style scoped>

</style>