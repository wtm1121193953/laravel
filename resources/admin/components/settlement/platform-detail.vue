<template>
    <page title="审核订单明细">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="交易时间" align="center"/>
            <el-table-column prop="order_no" label="订单编号" align="center"/>
            <el-table-column prop="goods_name" label="商品名称" align="center">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 3 && scope.row.dishes_items && scope.row.dishes_items.length == 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}
                    </span>
                    <span v-else-if="scope.row.type == 3 && scope.row.dishes_items && scope.row.dishes_items.length > 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}等{{getNumber(scope.row.dishes_items)}}件商品
                    </span>
                    <span v-else-if="scope.row.type == 2">
                        无
                    </span>
                    <span v-else>
                        {{scope.row.goods_name}}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="merchant.name|cs_merchant.name" label="商户" align="center"/>
            <el-table-column prop="oper.name" label="运营中心" align="center"/>
            <el-table-column prop="pay_price" label="订单金额" align="center"/>
            <el-table-column prop="settlement_rate" label="利率" width="100px" align="center">
                <template slot-scope="scope">
                    <span>{{ parseInt(scope.row.settlement_rate).toFixed(2) }}%</span>
                </template>
            </el-table-column>
            <el-table-column prop="settlement_real_amount" label="结算金额" align="center"/>
            <el-table-column prop="type" label="订单类型" align="center">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购订单</span>
                    <span v-else-if="scope.row.type == 2">扫码买单</span>
                    <span v-else-if="scope.row.type == 3">单品订单</span>
                    <span v-else-if="scope.row.type == 4">超市订单</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_type" label="支付方式" align="center">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.pay_type) === 1">微信</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 2">支付宝</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 3">融宝</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 4">钱包余额</span>
                    <span v-else>未知 ({{scope.row.pay_type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="订单状态" align="center">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">未支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已取消</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4">已支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">退款中[保留状态]</span>
                    <span v-else-if="parseInt(scope.row.status) === 6">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7">已完成[不可退款]</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getSettlementOrders"
                :page-size="15"
                :total="total"
        />
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "settlement-detail",
        props: {
            scope: {type: Object, required: true}
        },
        data() {
            return {
                isLoading: false,
                list: [],
                query: {
                    page: 1,
                    settlement_id: 0,
                    merchant_id: 0,
                },
                total: 0,
            }
        },
        methods: {
            getSettlementOrders() {
                api.get('/settlement/getPlatformOrders', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            init(){
                this.query.settlement_id = this.scope.id;
                this.query.merchant_id = this.scope.merchant_id;
                this.getSettlementOrders();
            }
        },
        created() {
            this.init();
        },
        watch: {
            scope: {
                deep: true,
                handler(){
                    this.init();
                }
            }
        }
    }
</script>

<style scoped>

</style>