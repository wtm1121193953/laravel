<template>
    <page title="分润列表">
        <el-form :model="query" inline size="small" @submit.native.prevent>
            <el-form-item prop="originId" label="分润用户ID">
                <el-input v-model="query.originId" clearable class="w-100"/>
            </el-form-item>
            <el-form-item prop="originType" label="分润用户类型">
                <el-select v-model="query.originType" size="small" placeholder="请选择" clearable class="w-150">
                    <el-option label="用户" value="1"/>
                    <el-option label="商户" value="2"/>
                    <el-option label="运营中心" value="3"/>
                    <el-option label="业务员" value="4"/>
                    <el-option label="超市" value="5"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="orderId" label="订单ID">
                <el-input v-model="query.orderId" clearable class="w-100"/>
            </el-form-item>
            <el-form-item prop="orderNo" label="订单号">
                <el-input v-model="query.orderNo" clearable/>
            </el-form-item>
            <el-form-item prop="type" label="分润类型">
                <el-select v-model="query.type" size="small" clearable placeholder="请选择" class="w-150">
                    <el-option label="自返" value="1"/>
                    <el-option label="返上级" value="2"/>
                    <el-option label="运营中心交易分润" value="3"/>
                    <el-option label="业务员分润" value="4"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="status" label="状态">
                <el-select v-model="query.status" size="small" clearable placeholder="请选择" class="w-150">
                    <el-option label="冻结中" value="1"/>
                    <el-option label="已解冻" value="2"/>
                    <el-option label="已退款返回" value="3"/>
                </el-select>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="list" :v-loading="tableLoading" stripe>
            <el-table-column prop="origin_id" label="分润用户ID"></el-table-column>
            <el-table-column prop="origin_type" label="分润用户类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.origin_type == 1">用户</span>
                    <span v-else-if="scope.row.origin_type == 2">商户</span>
                    <span v-else-if="scope.row.origin_type == 3">运营中心</span>
                    <span v-else-if="scope.row.origin_type == 4">业务员</span>
                    <span v-else-if="scope.row.origin_type == 5">超市</span>
                    <span v-else>未知({{scope.row.origin_type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="分润用户名称"></el-table-column>
            <el-table-column prop="order_id" label="订单ID"></el-table-column>
            <el-table-column prop="order_no" label="订单号"></el-table-column>
            <el-table-column prop="order_profit_amount" label="订单利润">
                <template slot-scope="scope">
                    {{scope.row.order_profit_amount}}元
                </template>
            </el-table-column>
            <el-table-column prop="ratio" label="分润比例">
                <template slot-scope="scope">
                    {{scope.row.ratio}}%
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="分润金额">
                <template slot-scope="scope">
                    {{scope.row.amount}}元
                </template>
            </el-table-column>
            <el-table-column prop="type" label="分润类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">自返</span>
                    <span v-else-if="scope.row.type == 2">返上级</span>
                    <span v-else-if="scope.row.type == 3">运营中心交易分润</span>
                    <span v-else-if="scope.row.type == 4">业务员分润</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">冻结中</span>
                    <span v-else-if="scope.row.status == 2">已解冻</span>
                    <span v-else-if="scope.row.status == 3">已退款返回</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="feeSplitting(scope.row)">重新分润</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
            class="fr m-t-20"
            layout="total, prev, pager, next"
            :current-page.sync="query.page"
            @current-change="getList"
            :page-size="query.pageSize"
            :total="total"
        ></el-pagination>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "fee_splitting_list",
        data() {
            return {
                list: [],
                total: 0,
                query: {
                    page: 1,
                    pageSize: 15,
                    originId: '',
                    originType: '',
                    orderId: '',
                    orderNo: '',
                    type: '',
                    status: '',
                },
                tableLoading: false,
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('/feeSplitting/getList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            feeSplitting(row) {
                api.post('/feeSplitting/ReFeeSplitting', {id: row.id}).then(data => {
                    this.$message.success('重新分润成功');
                    this.getList();
                })
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>