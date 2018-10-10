<template>
    <page title="运营中心营销报表">
        <el-col class="m-t-30 search-bar">

            <el-radio-group v-model="timeType" size="small">
                <el-radio label="all" border>全部</el-radio>
                <el-radio label="today" border>今日</el-radio>
                <el-radio label="yesterday" border>昨日</el-radio>
                <el-radio label="month" border>本月</el-radio>
                <el-radio label="lastMonth" border>上个月</el-radio>
                <el-radio label="other" border>其他时间</el-radio>
            </el-radio-group>
            <template v-if="timeType == 'other'">
                <el-date-picker
                        v-model="dateRange"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd"
                        size="small"
                >
                </el-date-picker>
                <el-button type="primary" size="small" @click="getList">查询</el-button>
            </template>

            <template>
                <el-select v-model="operId" filterable placeholder="请选择" size="small" @change="getList">
                    <el-option label="全部" value=0></el-option>
                    <el-option
                            v-for="item in opers"
                            :key="item.id"
                            :label="item.name"
                            :value="item.id">
                    </el-option>
                </el-select>
            </template>
            <el-button type="success" size="small" @click="dataExport">导出</el-button>
        </el-col>
        <el-alert class="m-t-15 m-b-15" v-if="timeType == 'today'" type="success" title="注: 当日数据每半个小时更新一次"/>
        <el-table :data="list" stripe v-loading="isLoading">
            <el-table-column prop="date" label="时间"/>
            <el-table-column prop="oper_id" label="运营中心id"/>
            <el-table-column prop="oper.name" label="运营中心名称"/>
            <el-table-column prop="merchant_num" label="商户数"/>
            <el-table-column prop="user_num" label="邀请用户数"/>
            <el-table-column prop="order_paid_num" label="总订单量（已支付）"/>
            <el-table-column prop="order_refund_num" label="总退款量"/>
            <el-table-column prop="order_paid_amount" label="总订单金额（已支付）"/>
            <el-table-column prop="order_refund_amount" label="总退款金额"/>

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
        name: "statistics-oper",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    startDate: '',
                    endDate: '',
                    timeType:'',
                    oper_id:0,
                },
                list: [],
                total: 0,
                originType: 'all',
                timeType: 'yesterday',
                dateRange: [],
                operId: '',
                searchDate: {},
                searchTypeName: '全部统计汇总 > 今日',

                opers:[]
            }
        },
        computed: {

        },
        methods: {
            getList(){
                if(this.timeType == 'other' && this.dateRange.length < 2){
                    this.$message.error('请选择起始日期');
                    return false;
                }
                this.query.timeType = this.timeType;
                this.query.oper_id = this.operId;

                this.query.startDate = this.dateRange[0] || '';
                this.query.endDate = this.dateRange[1] || '';

                this.isLoading = true;
                api.get('/statistics/oper', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            getSearchDate() {
                let startDate = '';
                let endDate = '';

                let now = new Date(); //当前日期
                let nowMonth = now.getMonth(); //当前月
                let nowYear = now.getFullYear(); //当前年

                if (this.timeType == 'today') {
                    startDate = endDate = new Date(new Date()).format('yyyy-MM-dd');
                } else if (this.timeType == 'yesterday') {
                    startDate = endDate = new Date(new Date().setDate(new Date().getDate() - 1)).format('yyyy-MM-dd');
                } else if (this.timeType == 'month') {
                    startDate = new Date(nowYear, nowMonth, 1).format('yyyy-MM-dd');
                    endDate = new Date(new Date(new Date().setMonth(nowMonth + 1)).setDate(0)).format('yyyy-MM-dd');
                } else if (this.timeType == 'lastMonth') {
                    startDate = new Date(new Date(new Date().setMonth(nowMonth - 1)).setDate(1)).format('yyyy-MM-dd');
                    endDate = new Date(new Date().setDate(0)).format('yyyy-MM-dd');
                } else if (this.timeType == 'other') {
                    startDate = this.dateRange[0] || '';
                    endDate = this.dateRange[1] || '';
                } else {
                    startDate = '';
                    endDate = '';
                }
                this.searchDate = {
                    startDate: startDate,
                    endDate: endDate,
                };
            },
            getOpers() {
                api.get('/statistics/all_opers').then(data => {
                    this.opers = data.list;
                })
            },
            dataExport() {
                let message = '确定要导出当前筛选的列表么？'
                if(this.timeType == 'other' && this.dateRange.length < 2){
                    this.$message.error('请选择起始日期');
                    return false;
                }
                this.query.timeType = this.timeType;
                this.query.oper_id = this.operId;

                this.query.startDate = this.dateRange[0] || '';
                this.query.endDate = this.dateRange[1] || '';

                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/statistics/oper_export?'
                        + 'timeType=' + this.query.timeType
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&oper_id=' + this.query.oper_id;

                })
            }
        },
        created(){
            this.getList();
            this.getOpers();
        },
        watch:{
            timeType(val){
                if(val != 'other'){
                    this.getList();
                }
                this.getSearchDate();
            },
        }

    }
</script>

<style scoped>

</style>