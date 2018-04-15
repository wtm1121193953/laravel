<template>
    <page title="订单管理" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"></el-table-column>
            <el-table-column prop="created_at" label="创建时间"></el-table-column>
            <el-table-column prop="order_no" label="订单号"></el-table-column>
            <el-table-column prop="goods_name" label="商品名称"></el-table-column>
            <el-table-column prop="pay_price" label="总价"></el-table-column>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1">未支付</span>
                    <span v-else-if="scope.row.status === 2">已取消</span>
                    <span v-else-if="scope.row.status === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="scope.row.status === 4">已支付</span>
                    <span v-else-if="scope.row.status === 5">退款中[保留状态]</span>
                    <span v-else-if="scope.row.status === 6">已退款</span>
                    <span v-else-if="scope.row.status === 7">已完成[不可退款]</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="详情">
                <template slot-scope="scope">
                    <el-button type="text" @click="showDetail(scope)">查看详情</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
            class="fr m-t-20"
            layout="total, prev, pager, next"
            :current-page.sync="query.page"
            @current-change="getList"
            :page-size="15"
            :total="total"
        ></el-pagination>

        <el-dialog title="订单详情" :visible.sync="isShow">
            <order-form :scope="order"></order-form>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import OrderForm from './form'

    export default {
        data() {
            return {
                isLoading: false,
                isShow: false,
                list: [],
                query: {
                    page: 1,
                },
                total: 0,
                order: {},
            }
        },
        methods: {
            getList() {
                api.get('/orders', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;

                    this.list['status'] = parseInt(this.list['status']);
                    this.list['created_at'] = new Date(this.list['created_at']).format('yyyy-MM-dd hh:mm:ss');
                })
            },
            showDetail(scope) {
                this.order = scope;
                this.isShow = true;
            }
        },
        created() {
            this.getList();
        },
        components: {
            OrderForm,
        }
    }
</script>

<style scoped>

</style>