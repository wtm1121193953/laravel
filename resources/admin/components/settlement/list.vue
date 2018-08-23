<template>

    <page title="结算列表【旧】" v-loading="isLoading">


        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户ID" >
                    <el-input v-model="query.merchantId" size="small"  placeholder="商户ID"  class="w-100" clearable></el-input>
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
            <el-table-column prop="created_at" label="添加时间"  width="160px" />
            <el-table-column prop="oper_id" size="mini"	 label="ID"/>
            <el-table-column prop="merchant_id" label=""/>
            <el-table-column prop="date" label="商户招牌名"/>
            <el-table-column prop="settlement_rate" size="mini" label="激活运营中心ID"/>
            <el-table-column prop="amount" label="激活运营中心名称"/>
            <el-table-column prop="charge_amount" label="业务员" />
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
        name: "settlement-list",
        data(){
            return {
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                unAudit:false,
                detailMerchant:null,
                query: {
                    name: '',
                    signboardName:'',
                    auditStatus: [],
                    page: 1,
                    merchantId: '',
                    startDate: '',
                    endDate: '',
                    operName:'',
                    operId:'',
                    creatorOperName:'',
                    creatorOperId:''
                },
                list: [],
                auditRecord:[],
                total: 0,
                currentMerchant: null,
                tableLoading: false,
            }
        },
        methods: {
            merchantChange(){
                this.getList();
            },
            showMessage(scope){
                api.get('/merchant/audit/record/newest', {id: scope.row.id}).then(data => {
                    this.auditRecord = [data];
                })
            },
            search() {
                if (this.query.startDate > this.query.endDate) {
                    this.$message.error('搜索的开始时间不能大于结束时间！');
                    return false;
                }
                //待审核页面bug修复
                if( this.query.auditStatus .length == 0  && this.isAudit ){
                    this.query.auditStatus = ['0', '3']
                }
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/settlements', params).then(data => {
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            detail(scope,type){
                let self = this;
                router.push({
                    path: '/merchant/detail',
                    name: 'MerchantDetail',
                    query: {
                        id: scope.row.id,
                        auditType: type,
                        isAudit: this.isAudit,
                    },
                    params: self.query,
                })
            },
            //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
            audit(scope, type){
                if(type==2 ||type==1){
                    api.get('merchant/detail', {id: scope.row.id}).then(data => {
                        this.detailMerchant = data;
                        this.detailMerchant.type = type;
                        this.unAudit = true;
                    });
                }else{
                    let message = ['', '审核通过', '审核不通过', '打回到商户池'][type];
                    this.$confirm(`确定 ${message} 吗?`, scope.row.name).then(() => {
                        api.post('/merchant/audit', {id: scope.row.id, type: type}).then(data => {
                            this.$alert(message + ' 操作成功');
                            this.getList();
                        })
                    });
                }

            },
            downloadExcel() {
                let message = '确定要导出当前筛选的商户列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/merchant/download?' + 'merchantId=' + this.query.merchantId + '&startDate=' + this.query.startDate + '&endDate=' + this.query.endDate + '&name=' + this.query.name + '&signboardName='+ this.query.signboardName+ '&auditStatus=' + this.query.auditStatus + '&operName=' + this.query.operName + '&operId=' + this.query.operId + '&creatorOperName=' + this.query.creatorOperName + '&creatorOperId=' + this.query.creatorOperId;
                })
            },
            changeStatus(row) {
                api.post('/merchant/changeStatus', {id: row.id}).then((data) => {
                    row.status = data.status;
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