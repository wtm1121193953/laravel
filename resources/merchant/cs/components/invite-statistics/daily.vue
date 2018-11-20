<template>
    <page title="用户分析">
        <div class="statistics-div">
            <div class="today-div">
                <div class="today-invite">
                    <div>
                        今日新增人数
                    </div>
                    <div class="today-number">
                        {{todayInviteCount}}
                    </div>
                </div>
            </div>
            <div class="total-div">
                <div class="total-invite">
                    <div>
                        总用户数
                    </div>
                    <div class="total-number">
                        {{totalInviteCount}}
                    </div>
                </div>
            </div>
        </div>
        <el-col>
            <el-table stripe :data="list">
                <el-table-column prop="date" label="日期"/>
                <el-table-column prop="invite_count" label="新增人数"/>
            </el-table>
            <el-pagination
                    class="fr m-t-20"
                    layout="total, prev, pager, next"
                    :current-page.sync="query.page"
                    @current-change="getList"
                    :page-size="query.pageSize"
                    :total="total"/>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'
    export default {
        name: "invite-statistics-daily",
        data() {
            return {
                list: [],
                total: 0,
                query: {
                    page: 1,
                    pageSize: 10,
                },
                todayInviteCount: 0,
                totalInviteCount: 0,
            }
        },
        methods: {
            getList(){
                this.loading = true;
                api.get('invite/statistics/dailyList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.loading = false;
                })
            },
            getTodayAndTotalInvite() {
                api.get('invite/statistics/getTodayAndTotalInviteNumber').then(data => {
                    this.todayInviteCount = data.todayInviteCount;
                    this.totalInviteCount = data.totalInviteCount;
                })
            }
        },
        mounted(){
            this.getList();
            this.getTodayAndTotalInvite();
        }
    }
</script>

<style scoped>
    .statistics-div {
        height: 200px;
        border: 1px solid #DCDFE6;
    }
    .today-div {
        width: 50%;
        height: 80%;
        border-right: 1px solid #606266;
        margin-top: 20px;
        float: left;
    }
    .total-div {
        width: 49%;
        height: 80%;
        margin-top: 20px;
        float: left;
    }
    .today-invite, .total-invite {
        margin-top: 50px;
    }

    .today-invite > *,.total-invite > * {
        display: block;
        text-align: center;
    }

    .today-number, .total-number {
        font-size: 50px;
    }
</style>