<template>
    <page title="结算详情">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="创建时间" align="center"/>
            <el-table-column prop="order_no" label="订单号" align="center"/>
            <el-table-column prop="goods_name" label="商品名称" align="center"/>
            <el-table-column prop="pay_price" label="总价" align="center"/>
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
                api.get('/getSettlementOrders', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            init(){
                this.query.settlement_id = this.scope.id;
                this.query.merchant_id = this.scope.merchant_id;
            }
        },
        created() {
            this.init();
            console.log(this.query)
            this.getSettlementOrders();
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