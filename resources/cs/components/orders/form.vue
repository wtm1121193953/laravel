<template>
    <div>
        <el-form label-width="150px">
            <el-form-item label="订单状态：">
                {{order.statusText}}
            </el-form-item>
            <el-form-item label="历时：">
                {{order.take_time}}
            </el-form-item>
            <el-form-item label="订单类型：">
                {{['','团购', '买单','单品', '超市'][order.type]}}
            </el-form-item>
            <el-form-item label="订单号：">
                {{order.order_no}}
            </el-form-item>
            <el-form-item label="所属商户：">
                {{order.merchant_name}}
            </el-form-item>
            <el-form-item label="商品信息：" v-if="order.type == 4">
                <div v-for="(item, index) in order.cs_order_goods" :key="index">
                    <span>{{item.goods_name}}</span>&nbsp;&nbsp;&nbsp;
                    <span>¥{{item.price}}</span>&nbsp;&nbsp;&nbsp;
                    <span>×{{item.number}}</span><br/>
                </div>
            </el-form-item>
            <el-form-item label="配送费：">
                {{order.deliver_price}}元
            </el-form-item>
            <el-form-item label="总价：">
                {{order.total_price}}元
            </el-form-item>
            <!--暂时使用discount_price字段-->
            <el-form-item label="免配送费：">
                {{order.discount_price}}元
            </el-form-item>
            <el-form-item label="实付：">
                {{order.pay_price}}元
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
            <el-form-item label="订单支付时间：">
                {{order.pay_time}}
            </el-form-item>
            <el-form-item label="支付方式：">
                {{['','微信', '支付宝','融宝','钱包余额'][order.pay_type]}}
            </el-form-item>
            <el-form-item label="发货方式：">
                {{['','配送', '自提'][order.deliver_type]}}
            </el-form-item>
            <span v-if="order.deliver_type == 1">
                <el-form-item label="快递公司：" v-if="order.status == 7 || order.status == 10">
                    {{order.express_company}}
                </el-form-item>
                <el-form-item label="快递单号：" v-if="order.status == 7 || order.status == 10">
                    {{order.express_no}}
                </el-form-item>
                <el-form-item label="收货人：">
                    {{order.express_address ? order.express_address.contacts : ''}}
                </el-form-item>
                <el-form-item label="收货人手机号码：">
                    {{order.express_address ? order.express_address.contact_phone : ''}}
                </el-form-item>
                <el-form-item label="地址：">
                    {{order.express_address ? order.express_address.province + order.express_address.city + order.express_address.area + order.express_address.address : ''}}
                </el-form-item>
                <el-form-item label="发货时间：" v-if="order.status == 7 || order.status == 10">
                    {{order.deliver_time}}
                </el-form-item>
                <el-form-item label="收货时间：" v-if="order.status == 7">
                    {{order.take_delivery_time}}
                </el-form-item>
            </span>
            <el-form-item label="订单取消时间：" v-if="order.status == 6">
                {{order.refund_time}}
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
                this.order.statusText = ['','未支付', '已取消', '已关闭[超时自动关闭]', '已支付', '退款中[保留状态]', '已退款', '已完成[不可退款]', '待发货', '待自提', '已发货'][parseInt(this.order.status)];
                this.order.created_at = new Date(this.order.created_at).format('yyyy-MM-dd hh:mm:ss');
                this.order.pay_time = new Date(this.order.pay_time).format('yyyy-MM-dd hh:mm:ss');
            },
        },
        created(){
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