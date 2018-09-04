<template>

    <page title="商户货款结算管理" v-loading="isLoading">
        <el-col style="margin-bottom: 10px;">
            <el-alert
                    title="温馨提示：单日订单金额小于100元，不生成结算单，总订单金额累计到100元后再生成结算单"
                    type="success"
                    close-text="知道了">
            </el-alert>
        </el-col>
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户名称" >
                    <el-input v-model="query.merchant_name" size="small"  placeholder="商户名称"  class="w-200" clearable></el-input>
                </el-form-item>
                <el-form-item prop="merchantId" label="商户ID" >
                    <el-input v-model="query.merchant_id" size="small"  placeholder="商户ID"  class="w-100" clearable></el-input>
                </el-form-item>

                <el-form-item prop="startDate" label="结算单生成时间：开始时间">
                    <el-date-picker
                            v-model="query.startDate"
                            type="date"
                            size="small"
                            placeholder="选择开始日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd"
                    ></el-date-picker>
                </el-form-item>
                <el-form-item prop="startDate" label="结束时间">
                    <el-date-picker
                            v-model="query.endDate"
                            type="date"
                            size="small"
                            placeholder="选择结束日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd"
                            :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                    ></el-date-picker>
                </el-form-item>
                <el-form-item label="结算状态" prop="status">
                    <el-select v-model="query.status" size="small"  multiple placeholder="请选择" class="w-150">
                        <el-option label="未打款" value="1"/>
                        <el-option label="打款中" value="2"/>
                        <el-option label="已打款" value="3"/>
                        <!--<el-option label="已到账" value="4"/>
                        <el-option label="打款失败" value="5"/>-->
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
                </el-form-item>
            </el-form>

        </el-col>

        <el-dialog :visible.sync="isShowSettlementDetail"  width="60%">
            <settlement-detail :scope="settlement"/>
        </el-dialog>

        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="merchant.id" label="商户ID"  width="100px" />
            <el-table-column prop="merchant.name" label="结算商户"  width="160px" />
            <el-table-column prop="created_at" label="结算单生成时间"/>
            <el-table-column prop="date" label="结算周期">
                <template slot-scope="scope">
                    {{scope.row.start_date}} 至 {{scope.row.end_date}}
                </template>
            </el-table-column>
            <el-table-column prop="oper.name" size="mini"	 label="运营中心"/>
            <!--<el-table-column prop="created_at" label="结算时间"/>-->
            <el-table-column prop="amount" size="mini" label="订单金额"/>
            <!--<el-table-column prop="settlement_rate" label="利率"/>-->
            <el-table-column prop="real_amount" label="结算金额" />
            <el-table-column prop="status_val" label="状态" />
            <!--<el-table-column prop="" label="备注" />-->
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" @click="showOrders(scope)">查看</el-button>
                    <el-button type="text"
                               v-if="parseInt(scope.row.status) === 1" @click="modifyPlatformStatus(scope)">确认打款</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"/>

    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import SettlementDetail from './platform-detail'

    export default {
        name: "settlement-platform",
        data(){
            return {
                isShowSettlementDetail: false,
                settlement: {},
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                unAudit:false,
                detailMerchant:null,
                query: {
                    merchant_name: '',
                    merchant_id:'',
                    status: '',
                    page: 1,
                    startDate: '',
                    endDate: '',
                    show_zero:true
                },
                list: [],
                auditRecord:[],
                total: 0,
                currentMerchant: null,
                tableLoading: false
            }
        },
        methods: {
            search() {
                if (this.query.startDate > this.query.endDate) {
                    this.$message.error('搜索的开始时间不能大于结束时间！');
                    return false;
                }
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/settlement/platforms', params).then(data => {
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            showOrders(scope) {
                this.settlement = scope.row;
                this.isShowSettlementDetail = true;
            },
            downloadExcel() {
                let message = '确定要导出当前筛选的商户列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/settlement/download?'
                        + 'merchant_name=' + this.query.merchant_name
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&merchant_id=' + this.query.merchant_id
                        + '&status='+ this.query.status
                        + '&show_zero=' + this.query.show_zero ;
                })
            },
            modifyPlatformStatus(scope){

                this.$confirm('确认此单已完成打款了吗，请再次确认?').then(() => {
                    api.get('/settlement/modifyStatus', {id: scope.row.id});
                    this.getList();
                })
            },
        },
        created(){

            Object.assign(this.query, this.$route.params);
            this.getList();

        },
        components: {
            SettlementDetail,
        }
    }
</script>

<style scoped>

</style>