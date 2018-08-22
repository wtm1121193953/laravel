<template>
    <page title="账户总览">
        <el-form :model="query" inline size="small">
            <el-form-item prop="billNo" label="交易号">
                <el-input v-model="query.billNo" clearable/>
            </el-form-item>
            <el-form-item label="交易时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        clearable
                >
                </el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        clearable
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                >
                </el-date-picker>
            </el-form-item>
            <el-form-item label="交易类型">
                <el-select v-model="query.type" placeholder="请选择" clearable class="w-150">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="提现" :value="1"></el-option>
                    <el-option label="用户消费返利" :value="2"></el-option>
                    <el-option label="用户消费返利退款" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="download">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="交易时间"></el-table-column>
            <el-table-column prop="bill_no" label="交易号"></el-table-column>
            <el-table-column prop="merchant_name" label="商户名称"></el-table-column>
            <el-table-column prop="type" label="交易类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 2">交易分润入账</span>
                    <span v-else-if="scope.row.type == 4">交易分润退款</span>
                    <span v-else-if="scope.row.type == 7">提现</span>
                    <span v-else-if="scope.row.type == 8">提现失败</span>
                    <span v-else>未知{{scope.row.type}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="交易状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 0">成功</span>
                    <span v-else-if="scope.row.status == 1">提现中</span>
                    <span v-else-if="scope.row.status == 2">提现成功</span>
                    <span v-else-if="scope.row.status == 3">提现失败</span>
                    <span v-else>未知{{scope.row.status}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="账户交易金额">
                <template slot-scope="scope">
                    <span v-if="scope.row.inout_type == 1">+</span>
                    <span v-else>-</span>
                    {{scope.row.amount}}
                </template>
            </el-table-column>
            <el-table-column prop="after_amount" label="账户余额"></el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope.row)">查 看</el-button>
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
        />
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-summary-list",
        data() {
            return {
                query: {
                    billNo: '',
                    startDate: '',
                    endDate: '',
                    type: 0,
                    page: 1,
                    pageSize: 15,
                },
                total: 0,
                list: [],
                tableLoading: false,
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('/wallet/bill/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            download() {

            },
            detail(row) {

            }
        },
        created() {
            this.getList();
        },
    }
</script>

<style scoped>

</style>