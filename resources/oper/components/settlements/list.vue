<template>
    <page title="财务管理" v-loading="isLoading">
        <el-form size="small" :model="query" inline>
            <el-form-item label="商户">
                <el-select v-model="query.merchantId">
                    <el-option label="全部" value=""/>
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>
            <el-form-item label="状态">
                <el-select v-model="query.status">
                    <el-option label="全部" value=""/>
                    <el-option label="未打款" :value="1"/>
                    <el-option label="已打款" :value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search"> 搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column prop="merchant_name" label="结算商户" align="center">
                <template slot-scope="scope">
                    {{scope.row.merchant_name}}
                    <el-button type="text"
                               v-if="!query.merchantId"
                               @click="() => {query.merchantId = scope.row.merchant_id; search();}"
                    >只看他的</el-button>
                </template>
            </el-table-column>
            <el-table-column prop="settlement_date" label="结算时间" align="center"/>
            <el-table-column prop="settlement_cycle" label="结算周期" align="center">
                <template slot-scope="scope">
                    {{scope.row.start_date}} 至 {{scope.row.end_date}}
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="订单金额" align="center"/>
            <el-table-column prop="settlement_rate" label="费率" align="center">
                <template slot-scope="scope">
                    {{scope.row.settlement_rate}} %
                </template>
            </el-table-column>
            <el-table-column prop="real_amount" label="结算金额" align="center"/>
            <el-table-column prop="status" label="结算状态" align="center">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">审核中</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已打款</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
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

        <el-dialog :visible.sync="isShowSettlementDetail">
            <settlement-detail :scope="settlement"/>
        </el-dialog>

        <el-dialog :visible.sync="isShowInvoice">
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
                },
                total: 0,
                settlement: {},
                merchants: [],
            }
        },
        methods: {
            getMerchants(){
                api.get('/merchants').then(data => {
                    this.merchants = data.list;
                })
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