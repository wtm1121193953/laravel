<template>
    <page title="超市商户账户交易记录" :breadcrumbs="{超市商户账户管理: '/wallet/cs_merchant'}">
        <el-form :model="query" inline size="small">
            <el-form-item prop="billNo" label="交易号">
                <el-input v-model="query.billNo" clearable placeholder="请输入交易号"/>
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
                    <el-option label="提现" :value="7"></el-option>
                    <el-option label="被分享人消费奖励" :value="2"></el-option>
                    <!--<el-option label="被分享人消费奖励退款" :value="4"></el-option>-->
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
                    <span v-if="scope.row.type == 1">自己消费奖励</span>
                    <span v-else-if="scope.row.type == 2">被分享人消费奖励</span>
                    <span v-else-if="scope.row.type == 3">自己消费奖励退款</span>
                    <span v-else-if="scope.row.type == 4">被分享人消费奖励退款</span>
                    <span v-else-if="scope.row.type == 5">交易分润入账</span>
                    <span v-else-if="scope.row.type == 6">交易分润退款</span>
                    <span v-else-if="scope.row.type == 7">
                        提现
                        <span v-if="scope.row.status == 1">(审核中)</span>
                        <span v-else-if="scope.row.status == 2">(审核通过)</span>
                        <span v-else-if="scope.row.status == 3">(已打款)</span>
                        <span v-else-if="scope.row.status == 4">(打款失败)</span>
                        <span v-else-if="scope.row.status == 5">(审核不通过)</span>
                    </span>
                    <span v-else-if="scope.row.type == 8">提现失败</span>
                    <span v-else>未知{{scope.row.type}}</span>
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
                    originType: 5, // 商户类型
                    walletId: null, // 钱包id

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
                let data = this.query;
                let params = [];
                Object.keys(data).forEach((key) => {
                    let value =  data[key];
                    if (typeof value === 'undefined' || value == null) {
                        value = '';
                    }
                    params.push([key, encodeURIComponent(value)].join('='))
                }) ;
                let uri = params.join('&');

                location.href = `/api/admin/wallet/bill/exportExcel?${uri}`;
            },
        },
        created() {
            this.query.walletId = this.$route.query.id;
            if (!this.query.walletId) {
                this.$message.error('id不能为空');
                router.push('/wallet/merchant');
                return;
            }
            this.getList();
        },
    }
</script>

<style lang="less" scoped>

</style>