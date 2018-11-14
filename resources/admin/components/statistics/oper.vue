<template>
    <page title="运营中心营销报表">
        <el-col class="m-t-30 search-bar">

            <el-radio-group v-model="timeType" size="small">
                <el-radio label="all" border>全部</el-radio>
                <!--<el-radio label="today" border>今日</el-radio>-->
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
        <el-col class="m-t-15 m-b-15">
            <el-alert v-if="timeType == 'today'" type="success" title="注: 当日数据每半个小时更新一次"/>
        </el-col>
        <el-table :data="list" stripe v-loading="isLoading" @sort-change="sortChange">
            <el-table-column prop="date" label="时间"/>
            <el-table-column prop="oper_id" label="运营中心id"/>
            <el-table-column prop="oper.name" label="运营中心名称"/>
            <el-table-column label="运营中心省份+城市">
                <template slot-scope="scope">
                    <span>{{scope.row.oper.province}}</span>
                    <span>{{scope.row.oper.city}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="user_num" label="运营中心邀请会员数">
                <template slot-scope="scope">
                    {{scope.row.user_num}}
                    <el-button type="text" @click="showInvite(scope.row)">查看</el-button>
                </template>
            </el-table-column>
            <el-table-column prop="merchant_invite_num" label="商户邀请会员数"/>
            <el-table-column prop="oper_and_merchant_invite_num" label="运营中心及商户共邀请会员数" sortable="custom"/>
            <el-table-column prop="merchant_total_num" label="商户总数" sortable="custom"/>
            <el-table-column prop="merchant_num" label="正式商户数" sortable="custom"/>
            <el-table-column prop="merchant_pilot_num" label="试点商户数" sortable="custom"/>
            <el-table-column label="总金额(已完成)/笔数">
                <template slot-scope="scope">
                    {{scope.row.order_paid_amount}}/{{scope.row.order_paid_num}}笔
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

    export default {
        name: "statistics-oper",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    startDate: '',
                    endDate: '',
                    timeType: '',
                    operId: 0,
                    staType: 3,
                    orderColumn: null,
                    orderType: null,
                },
                list: [],
                total: 0,
                originType: 'all',
                timeType: 'yesterday',
                dateRange: [],
                operId: '',
                searchDate: {},

                opers:[]
            }
        },
        computed: {

        },
        methods: {
            sortChange (column) {
                this.query.orderColumn = column.prop;
                this.query.orderType = column.order;
                this.getList();
            },
            getList(){
                if(this.timeType == 'other' && this.dateRange.length < 2){
                    this.$message.error('请选择起始日期');
                    return false;
                }
                this.query.timeType = this.timeType;
                this.query.operId = this.operId;

                this.query.startDate = this.dateRange[0] || '';
                this.query.endDate = this.dateRange[1] || '';

                this.isLoading = true;
                api.get('/statistics/list', this.query).then(data => {
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
                this.query.operId = this.operId;

                this.query.startDate = this.dateRange[0] || '';
                this.query.endDate = this.dateRange[1] || '';

                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/statistics/export?'
                        + 'timeType=' + this.query.timeType
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&operId=' + this.query.operId
                        + '&staType=' + this.query.staType;

                })
            },
            showInvite(row) {
                router.push({
                    path: '/statistics/invite/list',
                    query: {
                        originId: row.oper_id,
                        originType: 3,
                        name: row.oper.name,
                        timeType: this.query.timeType,
                        startDate: this.query.startDate,
                        endDate: this.query.endDate,
                    }
                });
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