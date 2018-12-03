<template>
    <page title="运营中心账户管理">
        <el-form v-model="form" inline size="small">
            <el-form-item prop="operName" label="运营中心名称">
                <el-input v-model="form.operName" clearable placeholder="请输入运营中心名称"/>
            </el-form-item>
            <el-form-item prop="operId" label="运营中心ID">
                <el-input v-model="form.operId" clearable placeholder="运营中心ID" class="w-100"/>
            </el-form-item>
            <el-form-item label="账户状态">
                <el-select v-model="form.status" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="正常" :value="1"></el-option>
                    <el-option label="已冻结" :value="2"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="oper_name" label="运营中心名称"></el-table-column>
            <el-table-column prop="origin_id" label="运营中心ID"></el-table-column>
            <el-table-column prop="amount" label="账户余额">
                <template slot-scope="scope">
                    {{(parseFloat(scope.row.balance) + parseFloat(scope.row.freeze_balance)).toFixed(2)}}
                </template>
            </el-table-column>
            <el-table-column prop="balance" label="可提现金额"></el-table-column>
            <el-table-column prop="freeze_balance" label="冻结金额"></el-table-column>
            <el-table-column prop="bank_card_no" label="银行账号" width="200px">
                <template slot-scope="scope">
                    <span v-if="scope.row.bank_card_no">
                        {{scope.row.bank_card_no.substr(0,5) + '****' + scope.row.bank_card_no.substr(-4,4)}}
                    </span>
                    (企业)
                </template>
            </el-table-column>
            <el-table-column prop="bank_open_name" label="账户名"></el-table-column>
            <el-table-column prop="sub_bank_name" label="开户行"></el-table-column>
            <el-table-column prop="status" label="账户状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">正常</span>
                    <span v-else-if="scope.row.status == 2">已冻结</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="changeStatus(scope.row)">
                        <span v-if="scope.row.status == 1">冻 结</span>
                        <span v-else>解 冻</span>
                    </el-button>
                    <el-button type="text" @click="detail(scope.row)">交易记录</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="form.page"
                @current-change="getList"
                :page-size="form.pageSize"
                :total="total"
        ></el-pagination>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-oper-list",
        data() {
            return {
                form: {
                    operName: '',
                    operId: '',
                    status: 0,

                    // 商户提现记录
                    originType: 3,

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
                api.get('/wallet/list', this.form).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.form.page = 1;
                this.getList();
            },
            exportExcel() {
                let data = this.form;
                let params = [];
                Object.keys(data).forEach((key) => {
                    let value =  data[key];
                    if (typeof value === 'undefined' || value == null) {
                        value = '';
                    }
                    params.push([key, encodeURIComponent(value)].join('='))
                }) ;
                let uri = params.join('&');

                location.href = `/api/admin/wallet/list/export?${uri}`;
            },
            detail(row) {
                router.push({
                    path: '/wallet/oper/bill',
                    query: {
                        id: row.id,
                    }
                });
            },
            changeStatus(row) {
                api.post('/wallet/list/changeStatus', {id: row.id}).then(data => {
                    row.status = data.status;
                    let msg = data.status == 1 ? '解冻成功' : '冻结成功';
                    this.$message.success(msg);
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