<template>
    <div>
        <el-form label-width="150px">
            <el-form-item label="订单号：">
                {{order.order_no}}
            </el-form-item>
            <el-form-item label="订单类型：">
                {{['','团购', '买单','单品'][order.type]}}
            </el-form-item>
            <el-form-item label="商品名称：" v-if="order.type == 1">
                {{order.goods_name}}
            </el-form-item>

            <el-form-item label="商品信息：" v-if="order.type == 3">
                {{order.goods_name}}
                <!--<div v-for="item in order.dishes.items" :key="item">-->
                    <!---->
                <!--</div>-->
            </el-form-item>

            <el-form-item label="所属店铺：">
                {{order.merchant_name}}
            </el-form-item>
            <el-form-item label="单价：" v-if="order.type == 1">
                {{order.price}}
            </el-form-item>
            <el-form-item label="数量：" v-if="order.type == 1">
                {{order.buy_number}}
            </el-form-item>
            <el-form-item label="总价：">
                {{order.pay_price}}
            </el-form-item>
            <!--<el-form-item label="身份：">
                {{order.user_level_text}}
            </el-form-item>
            <el-form-item label="返利积分：">
                {{order.credit}}
            </el-form-item>-->
            <el-form-item label="订单状态：">
                {{order.statusText}}
            </el-form-item>
            <el-form-item label="手机号：">
                {{order.notify_mobile}}
            </el-form-item>
            <el-form-item label="备注：">
                {{order.remark}}
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
        methods:{
            initOrder(){
                this.order = deepCopy(this.scope);
                this.order.statusText = ['','未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成[不可退款]'][parseInt(this.order.status)];
                this.order.created_at = new Date(this.order.created_at).format('yyyy-MM-dd hh:mm:ss');
            }
        },
        created() {
            this.initOrder();
        },
        watch: {
            scope(){
                this.initOrder()
            }
        }

    }
</script>

<style scoped>

</style>