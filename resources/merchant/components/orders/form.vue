<template>
    <div>
        <el-form label-width="150px">
            <el-form-item label="订单号：">
                {{order.order_no}}
            </el-form-item>
            <el-form-item label="商品名称：">
                {{order.goods_name}}
            </el-form-item>
            <el-form-item label="所属店铺：">
                {{order.merchant_name}}
            </el-form-item>
            <el-form-item label="单价：">
                {{order.price}}
            </el-form-item>
            <el-form-item label="数量：">
                {{order.buy_number}}
            </el-form-item>
            <el-form-item label="总价：">
                {{order.pay_price}}
            </el-form-item>
            <el-form-item label="订单状态：">
                {{order.status}}
            </el-form-item>
            <el-form-item label="手机号：">
                {{order.notify_mobile}}
            </el-form-item>
            <el-form-item label="订单创建时间：">
                {{order.created_at}}
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    export default {
        props: {
            scope: {type: Object, required: true}
        },
        data() {
            return {
                order: {},
            }
        },
        created() {
            this.order = deepCopy(this.scope.row);
            this.order.status = ['','未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成[不可退款]'][parseInt(this.order.status)];
            this.order.created_at = new Date(this.order.created_at).format('yyyy-MM-dd hh:mm:ss');
        }
    }
</script>

<style scoped>

</style>