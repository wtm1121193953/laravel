<template>
    <page title="用户营销报表">
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
            </template>

            <template>
                <!--<el-select v-model="userId" filterable placeholder="请选择" size="small" @change="getList">
                    <el-option label="全部" value=0></el-option>
                    <el-option
                            v-for="item in users"
                            :key="item.id"
                            :label="item.mobile"
                            :value="item.id">
                    </el-option>
                </el-select>-->
                <el-input v-model="userId" size="small" clearable class="w-200" placeholder="请输入用户ID"></el-input>
            </template>
            <el-button type="primary" size="small" @click="search">查询</el-button>
            <el-button type="success" size="small" @click="dataExport">导出</el-button>
        </el-col>
        <el-col class="m-t-15 m-b-15">
            <el-alert v-if="timeType == 'today'" type="success" title="注: 当日数据每半个小时更新一次"/>
        </el-col>
        <el-table :data="list" stripe v-loading="isLoading" @sort-change="sortChange">
            <el-table-column prop="date" label="时间"/>
            <el-table-column prop="user_id" label="用户ID"/>
            <el-table-column prop="user.mobile" label="用户手机号码"/>
            <el-table-column prop="invite_user_num" label="邀请会员数" sortable="custom">
                <template slot-scope="scope">
                    {{scope.row.invite_user_num}}
                    <el-button type="text" @click="showInvite(scope.row)">查看</el-button>
                </template>
            </el-table-column>
            <el-table-column label="总金额(已完成)/笔数">
                <template slot-scope="scope">
                    {{scope.row.order_finished_amount}}/{{scope.row.order_finished_num}}笔
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
                    userId: 0,
                    staType: 1,
                    orderColumn: null,
                    orderType: null,
                },
                list: [],
                total: 0,
                originType: 'all',
                timeType: 'yesterday',
                dateRange: [],
                userId: '',
                searchDate: {},

                users:[]
            }
        },
        computed: {

        },
        methods: {
            search() {
                this.query.page = 1;
                this.getList();
            },
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
                if (this.userId == '') {
                    this.$message.error('请输入用户ID');
                    return false;
                }
                this.query.timeType = this.timeType;
                this.query.userId = this.userId;


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
            dataExport() {
                let message = '确定要导出当前筛选的列表么？'
                if(this.timeType == 'other' && this.dateRange.length < 2){
                    this.$message.error('请选择起始日期');
                    return false;
                }
                this.query.timeType = this.timeType;
                this.query.userId = this.userId;

                this.query.startDate = this.dateRange[0] || '';
                this.query.endDate = this.dateRange[1] || '';

                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/statistics/export?'
                        + 'timeType=' + this.query.timeType
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&userId=' + this.query.userId
                        + '&staType=' + this.query.staType;
                })
            },
            showInvite(row) {
                router.push({
                    path: '/statistics/invite/list',
                    query: {
                        originId: row.user_id,
                        originType: 1,
                        name: row.user.mobile,
                        timeType: this.query.timeType,
                        startDate: this.query.startDate,
                        endDate: this.query.endDate,
                    }
                });
            }
        },
        created(){
            this.getList();
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