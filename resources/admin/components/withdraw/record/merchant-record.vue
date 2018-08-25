<template>
    <el-col>
        <el-form v-model="form" inline size="small">
            <el-form-item prop="withdrawNo" label="订单编号">
                <el-input v-model="form.withdrawNo" clearable class="w-200"/>
            </el-form-item>
            <el-form-item prop="merchantName" label="商户名称">
                <el-input v-model="form.merchantName" clearable/>
            </el-form-item>
            <el-form-item prop="merchantId" label="商户ID">
                <el-input v-model="form.merchantId" clearable class="w-100"/>
            </el-form-item>
            <el-form-item prop="bankCardType" label="账户类型">
                <el-select v-model="form.bankCardType" placeholder="请选择" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="公司" :value="1"></el-option>
                    <el-option label="个人" :value="2"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item prop="operName" label="运营中心名称">
                <el-input v-model="form.operName" clearable/>
            </el-form-item>
            <el-form-item label="提现时间">
                <el-date-picker
                    v-model="form.startDate"
                    type="date"
                    placeholder="选择开始日期"
                    format="yyyy 年 MM 月 dd 日"
                    value-format="yyyy-MM-dd"
                ></el-date-picker>
                <span>—</span>
                <el-date-picker
                    v-model="form.endDate"
                    type="date"
                    placeholder="选择结束日期"
                    format="yyyy 年 MM 月 dd 日"
                    value-format="yyyy-MM-dd"
                    :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(form.startDate) - 8.64e7}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item label="提现状态">
                <el-select v-model="form.status" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="审核中" :value="1"></el-option>
                    <el-option label="审核通过" :value="2"></el-option>
                    <el-option label="已打款" :value="3"></el-option>
                    <el-option label="打款失败" :value="4"></el-option>
                    <el-option label="审核不通过" :value="5"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="提现时间"></el-table-column>
            <el-table-column prop="withdraw_no" label="订单编号"></el-table-column>
            <el-table-column prop="amount" label="提现金额"></el-table-column>
            <el-table-column prop="charge_amount" label="手续费"></el-table-column>
            <el-table-column prop="remit_amount" label="到账金额"></el-table-column>
            <el-table-column label="发票快递信息">
                <template slot-scope="scope">
                    <span v-if="scope.row.bank_card_type == 2">无需发票</span>
                    <span v-else-if="scope.row.bank_card_type == 1">
                        <p>{{scope.row.invoice_express_company}}</p>
                        <p>{{scope.row.invoice_express_no}}</p>
                    </span>
                    <span v-else>未知</span>
                </template>
            </el-table-column>
            <el-table-column prop="bank_card_type" label="账户类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.bank_card_type == 2">个人</span>
                    <span v-else-if="scope.row.bank_card_type == 1">公司</span>
                    <span v-else>未知({{scope.row.bank_card_type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="merchant_name" label="商户名称"></el-table-column>
            <el-table-column prop="origin_id" label="商户ID"></el-table-column>
            <el-table-column prop="oper_name" label="运营中心"></el-table-column>
            <el-table-column prop="status" label="提现状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">审核中</span>
                    <span v-else-if="scope.row.status == 2">审核通过</span>
                    <span v-else-if="scope.row.status == 3">已打款</span>
                    <span v-else-if="scope.row.status == 4">打款失败</span>
                    <span v-else-if="scope.row.status == 5">审核不通过</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail">查看明细</el-button>
                    <el-button type="text" v-if="scope.row.status == 1" @click="audit">审 核</el-button>
                    <el-button type="text" v-if="scope.row.status == 2" @click="success">打款成功</el-button>
                    <el-button type="text" v-if="scope.row.status == 2" @click="failed">打款失败</el-button>
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
    </el-col>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "withdraw-merchant-record",
        data() {
            return {
                form: {
                    withdrawNo: '',
                    merchantName: '',
                    merchantId: '',
                    bankCardType: 0,
                    operName: '',
                    startDate: '',
                    endDate: '',
                    status: 0,

                    // 商户提现记录
                    originType: 2,

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
                api.get('/withdraw/records', this.form).then(data => {
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
                    if (typeof value === 'undefined') {
                        value = '';
                    }
                    params.push([key, encodeURIComponent(value)].join('='))
                }) ;
                let uri = params.join('&');

                location.href = `/api/admin/withdraw/record/export?${uri}`;
            },
            detail() {

            },
            audit() {

            },
            success() {

            },
            failed() {

            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>