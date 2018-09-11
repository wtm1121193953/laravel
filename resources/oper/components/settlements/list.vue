<template>
    <page title="财务管理" v-loading="isLoading">
        <el-form size="small" :model="query" inline>
            <el-form-item label="商户">
                <el-select v-model="query.merchantId" filterable clearable>
                    <el-option label="全部" value=""/>
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>
            <el-form-item label="状态">
                <el-select v-model="query.status" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="未打款" :value="1"/>
                    <el-option label="已打款" :value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item label="结算金额为0的数据">
                <el-select v-model="query.showAmount" clearable>
                    <el-option label="显示" value=""/>
                    <el-option label="不显示" :value="1"/>
                </el-select>
            </el-form-item>
            <el-form-item label="业务员">
                <el-input v-model="query.oper_biz_member_name" clearable placeholder="请输入业务员"/>
            </el-form-item>
            <el-form-item label="业务员手机号码">
                <el-input v-model="query.oper_biz_member_mobile" clearable placeholder="请输入业务员手机号码"/>
            </el-form-item>
            <el-form-item prop="settlement_date" label="结算时间">
                <el-date-picker
                        v-model="query.settlement_date"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search"> 搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr m-l-20" type="success" @click="exportExcel">导出Excel</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="merchant_name" label="结算商户" align="center">
                <template slot-scope="scope">
                    {{scope.row.merchant_name}}
                    <el-button type="text"
                               v-if="!query.merchantId"
                               @click="selectMerchant(scope.row.merchant_id)"
                    >只看他的</el-button>
                </template>
            </el-table-column>
            <el-table-column prop="settlement_date" label="结算时间" align="center"/>
            <el-table-column prop="settlement_cycle" label="结算周期" align="center" width="200px">
                <template slot-scope="scope">
                    {{scope.row.start_date}} 至 {{scope.row.end_date}}
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="订单金额 ¥" align="center"/>
            <el-table-column prop="settlement_rate" label="费率" align="center">
                <template slot-scope="scope">
                    {{scope.row.settlement_rate}} %
                </template>
            </el-table-column>
            <el-table-column prop="real_amount" label="结算金额 ¥" align="center"/>
            <el-table-column prop="status" label="结算状态" align="center">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">审核中</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已打款</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="业务员信息" align="center">
                <template slot-scope="scope">
                    {{scope.row.oper_biz_member_name}}/{{scope.row.oper_biz_member_mobile}}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="300px" align="center">
                <template slot-scope="scope">
                    <el-button type="text" @click="showOrders(scope)">审核订单</el-button>
                    <el-button type="text" v-if="parseInt(scope.row.status) === 1" @click="uploadInvoice(scope)">上传发票</el-button>
                    <el-button type="text" v-if="parseInt(scope.row.status) === 1" @click="payMoney(scope)">确认打款</el-button>
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
        />

        <el-dialog :visible.sync="isShowSettlementDetail" width="60%">
            <settlement-detail :scope="settlement"/>
        </el-dialog>

        <el-dialog :visible.sync="isShowInvoice"  >
            <invoice :scope="settlement" @cancel="isShowInvoice = false" @save="addInvoice"/>
        </el-dialog>

        <el-dialog :visible.sync="isShowPayMoney">
            <pay-money :scope="settlement" @cancel="isShowPayMoney = false" @save="addPayPicUrl"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import SettlementDetail from './settlement-detail'
    import Invoice from './invoice'
    import PayMoney from './pay-money'

    export default {
        data() {
            return {
                isLoading: false,
                isShowSettlementDetail: false,
                isShowInvoice: false,
                isShowPayMoney: false,
                list: [],
                query: {
                    page: 1,
                    merchantId: '',
                    status: '',
                    showAmount: '',
                    oper_biz_member_name: '',
                    oper_biz_member_mobile: '',
                    settlement_date: '',
                },
                total: 0,
                settlement: {},
                merchants: [],
            }
        },
        methods: {
            exportExcel(){
                // 导出操作
                let message = '确定导出全部财务列表么?'
                if(this.query.merchantId || this.query.status || this.query.showAmount || this.query.oper_biz_member_name || this.query.oper_biz_member_mobile || this.query.settlement_date){
                    message = '确定导出当前筛选的财务列表么?'
                }
                this.$confirm(message).then(() => {
                    window.open('/api/oper/settlements/export?merchantId=' + this.query.merchantId + '&status=' + this.query.status + '&showAmount=' + this.query.showAmount + '&operName=' + this.query.oper_biz_member_name + '&operMobile=' + this.query.oper_biz_member_mobile + '&settlementDate=' + this.query.settlement_date)
                })
            },
            getMerchants(){
                api.get('/merchant/allNames').then(data => {
                    this.merchants = data.list;
                })
            },
            selectMerchant(merchantId){
                this.query.merchantId = merchantId;
                this.search();
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList() {
                this.isLoading = true;
                api.get('/settlements', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.isLoading = false;
                })
            },
            showOrders(scope) {
                this.settlement = scope.row;
                this.isShowSettlementDetail = true;
            },
            uploadInvoice(scope) {
                this.isShowInvoice = true;
                this.settlement = scope.row;
            },
            addInvoice() {
                this.isShowInvoice = false;
                this.getList();
            },
            payMoney(scope) {
                if(parseInt(scope.row.invoice_type) === 0){
                    this.$message.error('请先上传发票');
                    return false;
                }
                this.isShowPayMoney = true;
                this.settlement = scope.row;
            },
            addPayPicUrl() {
                this.isShowPayMoney = false;
                this.getList();
            }
        },
        created() {
            this.getList();
            this.getMerchants();
        },
        components: {
            SettlementDetail,
            Invoice,
            PayMoney,
        }
    }
</script>