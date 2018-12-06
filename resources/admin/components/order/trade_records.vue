<template>
    <page title="交易记录">
        <el-form inline :model="query" size="small">
            <el-form-item label="订单号">
                <el-input type="text" clearable v-model="query.order_no" placeholder="请输入订单号"/>
            </el-form-item>
            <el-form-item label="所属运营中心ID">
                <el-input type="text" clearable placeholder="请输入所属运营中心ID" v-model="query.oper_id" class="w-150"/>
            </el-form-item>
            <el-form-item label="所属商户ID">
                <el-input type="text" clearable placeholder="请输入所属商户ID" v-model="query.merchant_id" class="w-150"/>
            </el-form-item>
            <el-form-item label="商户类型">
                <el-select v-model="query.merchant_type">
                    <el-option label="全部" value=""/>
                    <el-option
                            v-for="item in merchant_type_select"
                            :label="item.title"
                            :key="item.value"
                            :value="item.value">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="交易时间">
                <el-date-picker
                        class="w-150"
                        v-model="query.startTime"
                        type="date"
                        placeholder="开始日期"
                        value-format="yyyy-MM-dd 00:00:00"

                />
                -
                <el-date-picker
                        class="w-150"
                        v-model="query.endTime"
                        type="date"
                        placeholder="结束日期"
                        value-format="yyyy-MM-dd 23:59:59"
                />
            </el-form-item>
            <el-form-item label="用户ID">
                <el-input type="text" clearable v-model="query.user_id" placeholder="请输入用户ID"/>
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
            <el-table-column prop="trade_time" label="交易时间"/>
            <el-table-column prop="trade_no" label="交易流水" width="300px"/>
            <el-table-column prop="order_no" label="订单编号" width="200px"/>
            <el-table-column prop="trade_amount" label="交易金额¥" width="100px"/>
            <el-table-column prop="type" label="交易类型" width="100px">
                <template slot-scope="scope">
                    <span v-if="scope.row.type === 1" class="c-green">支付</span>
                    <span v-else-if="scope.row.type === 2" class="c-warning">退款</span>
                </template>
            </el-table-column>
            <el-table-column label="交易商户">
                <template slot-scope="scope">
                    <span v-if="scope.row.merchant">{{scope.row.merchant.name}}</span>
                    <span v-else-if="scope.row.cs_merchant">{{scope.row.cs_merchant.name}}</span>
                    <span v-else></span>
                </template>
            </el-table-column>
            <el-table-column prop="oper.name" label="所属运营中心"/>
            <el-table-column prop="user_id" label="用户ID"/>
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
        name: "trade-records",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    order_no: '',
                    oper_id: '',
                    merchant_id: '',
                    startTime: '',
                    endTime: '',
                    user_id: '',
                    merchant_type:'',
                },
                list: [],
                total: 0,
                opers:[],
                merchants: [],
                merchant_type_select:[{value:1,title:'普通'},{value:2,title:'超市'}]
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
                location.href = '/api/admin/trade_records/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                let queryData = deepCopy(this.query);
                api.get('/trade_records', queryData).then(data => {
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