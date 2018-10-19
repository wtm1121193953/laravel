<template>
    <page title="交易记录" v-loading="isLoading">
        <el-form inline :model="query" size="small">
            <el-form-item label="订单号">
                <el-input type="text" clearable v-model="query.order_no"/>
            </el-form-item>
            <el-form-item label="所属运营中心">
                <el-select v-model="query.oper_id" filterable clearable >
                    <el-option value="" label="全部"/>
                    <el-option v-for="item in opers" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>
            <el-form-item label="所属商户">
                <el-select v-model="query.merchantId" filterable clearable >
                    <el-option value="" label="全部"/>
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
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
            <el-form-item label="用户">
                <el-input type="text" clearable v-model="query.mobile"/>
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
            <el-table-column prop="trade_no" label="交易流水"/>
            <el-table-column prop="order_no" label="订单编号"/>
            <el-table-column prop="trade_amount" label="交易金额¥"/>
            <el-table-column prop="type" label="交易类型"/>
            <el-table-column prop="merchant.name" label="交易商户"/>
            <el-table-column prop="oper.name" label="所属运营中心"/>
            <el-table-column prop="user.mobile" label="用户"/>
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" @click="">明细</el-button>
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