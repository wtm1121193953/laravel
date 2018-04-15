<template>
    <page title="财务管理" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="merchant_name" label="结算商户" align="center"></el-table-column>
            <el-table-column prop="settlement_date" label="结算时间" align="center"></el-table-column>
            <el-table-column prop="settlement_cycle" label="结算周期" align="center">
                <template slot-scope="scope">
                    {{scope.row.start_date}} 至 {{scope.row.end_date}}
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="订单金额" align="center"></el-table-column>
            <el-table-column prop="settlement_rate" label="利率" align="center"></el-table-column>
            <el-table-column prop="real_amount" label="结算金额" align="center"></el-table-column>
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
                    <el-button type="text" @click="uploadInvoice(scope)">上传发票</el-button>
                    <el-button type="text">确认打款</el-button>
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

        <el-dialog :visible.sync="isShowSettlementDetail">
            <settlement-detail :scope="settlement"></settlement-detail>
        </el-dialog>

        <el-dialog :visible.sync="isShowInvoice">
            <invoice :scope="settlement" @cancel="isShowInvoice = false" @save="addInvoice"></invoice>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import SettlementDetail from './settlement-detail'
    import Invoice from './invoice'

    export default {
        data() {
            return {
                isLoading: false,
                isShowSettlementDetail: false,
                isShowInvoice: false,
                list: [],
                query: {
                    page: 1,
                },
                total: 0,
                settlement: {},
            }
        },
        methods: {
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

            }
        },
        created() {
            this.getList();
        },
        components: {
            SettlementDetail,
            Invoice,
        }
    }
</script>