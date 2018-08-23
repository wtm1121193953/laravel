<template>

    <page title="结算列表【新】" v-loading="isLoading">


        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户名称" >
                    <el-input v-model="query.merchant_name" size="small"  placeholder="商户名称"  class="w-200" clearable></el-input>
                </el-form-item>
                <el-form-item prop="merchantId" label="商户ID" >
                    <el-input v-model="query.merchant_id" size="small"  placeholder="商户ID"  class="w-100" clearable></el-input>
                </el-form-item>

                <el-form-item prop="startDate" label="结算开始时间">
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
                        <el-option label="打款失败" value="4"/>
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


        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="merchant.name" label="结算商户"  width="160px" />
            <el-table-column prop="oper.name" size="mini"	 label="运营中心"/>
            <el-table-column prop="created_at" label="结算时间"/>
            <el-table-column prop="date" label="结算订单日期"/>
            <el-table-column prop="amount" size="mini" label="订单金额"/>
            <el-table-column prop="settlement_rate" label="利率"/>
            <el-table-column prop="real_amount" label="结算金额" />
            <el-table-column prop="status_val" label="状态" />
            <el-table-column prop="" label="备注" />
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text">查看</el-button>
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
    export default {
        name: "settlement-platform",
        data(){
            return {
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
                    endDate: ''
                },
                list: [],
                auditRecord:[],
                total: 0,
                currentMerchant: null,
                tableLoading: false,
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

            downloadExcel() {
                let message = '确定要导出当前筛选的商户列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/merchant/download?' + 'merchantId=' + this.query.merchantId + '&startDate=' + this.query.startDate + '&endDate=' + this.query.endDate + '&name=' + this.query.name + '&signboardName='+ this.query.signboardName+ '&auditStatus=' + this.query.auditStatus + '&operName=' + this.query.operName + '&operId=' + this.query.operId + '&creatorOperName=' + this.query.creatorOperName + '&creatorOperId=' + this.query.creatorOperId;
                })
            }
        },
        created(){

            Object.assign(this.query, this.$route.params);
            this.getList();


        },
    }
</script>

<style scoped>

</style>