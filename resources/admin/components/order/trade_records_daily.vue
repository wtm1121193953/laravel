<template>
    <page title="交易汇总" v-loading="isLoading">
        <el-form inline :model="query" size="small">
            <el-form-item label="交易日">
                <el-date-picker
                        class="w-150"
                        v-model="query.startTime"
                        type="date"
                        placeholder="开始日期"
                        value-format="yyyy-MM-dd"

                />
                -
                <el-date-picker
                        class="w-150"
                        v-model="query.endTime"
                        type="date"
                        placeholder="结束日期"
                        value-format="yyyy-MM-dd"
                />
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">
                    <i class="el-icon-search"></i> 搜索
                </el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" class="m-l-30" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe v-loading="isLoading">
            <el-table-column prop="sum_date" label="交易日"/>
            <el-table-column prop="pays" label="实收金额/笔数"/>
            <el-table-column prop="refunds" label="退款金额/笔数"/>
            <el-table-column prop="income" label="收益"/>
            <el-table-column prop="updated_at" label="统计时间"/>
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

    const time = new Date()
    const day = time.getFullYear() + '-' + (time.getMonth() + 1) + '-' + time.getDate()

    export default {
        name: "trade-records",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    order_no: '',
                    oper_id: '',
                    merchant_id: '',
                    startTime: day,
                    endTime: day,
                    mobile: '',
                },
                list: [],
                total: 0,
                opers:[],
                merchants: [],
            }
        },
        computed: {

        },
        methods: {
            exportExcel(){
                let array = [];
                for (let key in this.query){
                    array.push(key + '=' + this.query[key]);
                }
                location.href = '/api/admin/trade_records_daily/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                let queryData = deepCopy(this.query);
                api.get('/trade_records_daily', queryData).then(data => {
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            getOptions(){
                api.get('/getOptions').then(data => {
                    this.opers = data.list.opers;
                    this.merchants = data.list.merchants;
                })
            },
            getNumber(row) {
                let num = 0;
                row.forEach(function (item) {
                    num = num + item.number;
                })
                return num;
            }
        },
        created(){
            this.getList();
            this.getOptions();
        },
        components: {
        }
    }
</script>

<style>
    .table-expand {
        font-size: 0;
    }
    .table-expand label {
        width: 90px;
        color: #99a9bf;
    }
    .table-expand .el-form-item {
        margin-right: 0;
        margin-bottom: 0;
        width: 50%;
    }
</style>

<style scoped>

</style>