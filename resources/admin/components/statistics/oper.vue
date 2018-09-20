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
                <el-button type="primary" size="small" @click="getData">查询</el-button>
            </template>

        </el-col>
        <el-table :data="list" stripe>
            <el-table-column prop="date" label="时间"/>
            <<el-table-column prop="oper_id" label="运营中心id"/>
            <el-table-column prop="oper_name" label="运营中心名称"/>
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
                },
                list: [],
                total: 0
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/statistics/oper', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },

        },
        created(){
            this.getList();
        },
    }
</script>

<style scoped>

</style>