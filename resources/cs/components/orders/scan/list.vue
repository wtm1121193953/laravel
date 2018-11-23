<template>
    <page title="买单订单管理" v-loading="isLoading">
        <el-row align="bottom" type="flex">
            <el-col :span="22">
                <el-form inline :model="query" size="small">
                    <el-form-item label="订单号">
                        <el-input type="text" clearable v-model="query.orderNo" placeholder="输入订单号"/>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input type="text" clearable v-model="query.mobile" placeholder="输入手机号" class="w-150"/>
                    </el-form-item>

                    <el-form-item label="支付时间">
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
                                :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startTime)}}"
                        />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="search">
                            <i class="el-icon-search"></i> 搜索
                        </el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="success" class="m-l-30" @click="exportExcel">导出Excel</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <el-table :data="list" stripe>
            <el-table-column prop="pay_time" width="250" label="支付时间"/>
            <el-table-column prop="order_no" width="250" label="订单号"/>
            <el-table-column prop="type" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购订单</span>
                    <span v-else-if="scope.row.type == 2">扫码订单</span>
                    <span v-else-if="scope.row.type == 3">点菜订单</span>
                    <span v-else-if="scope.row.type == 4">超市订单</span>
                    <span v-else>其他({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_price" label="总价 ¥"/>
            <el-table-column prop="notify_mobile" label="手机号"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-danger">未支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已取消</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4" class="c-green" >已支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">退款中[保留状态]</span>
                    <span v-else-if="parseInt(scope.row.status) === 6">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7">已完成</span>
                    <span v-else-if="parseInt(scope.row.status) === 8" class="c-danger">待发货</span>
                    <span v-else-if="parseInt(scope.row.status) === 9" class="c-danger">待自提</span>
                    <span v-else-if="parseInt(scope.row.status) === 10" class="c-green">已发货</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_type" label="支付方式">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.pay_type) === 1">微信</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 2">支付宝</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 3">融宝</span>
                    <span v-else-if="parseInt(scope.row.pay_type) === 4">钱包余额</span>
                    <span v-else>未知 ({{scope.row.pay_type}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="200">
                <template slot-scope="scope">
                    <el-button type="text" @click="showDetail(scope.row)">订单详情</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"
        />

        <el-dialog title="订单详情" :visible.sync="isShow">
            <order-form :scope="order"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'
    import OrderForm from './form'

    export default {
        data() {
            return {
                isLoading: false,
                isShow: false,
                list: [],
                query: {
                    page: 1,
                    orderNo: '',
                    mobile: '',
                    timeType: 'payTime',
                    startTime: '',
                    endTime: '',
                    status: '',
                    merchantType: 2,
                    type: 2,
                },
                total: 0,
                order: {},

            }
        },
        methods: {
            exportExcel(){
                // 导出操作
                let array = [];
                for (let key in this.query){
                    array.push(key + '=' + this.query[key]);
                }
                location.href = '/api/cs/orders/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList() {
                this.isLoading = true;
                api.get('/orders', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;

                    this.isLoading = false;
                })
            },
            showDetail(scope) {
                this.order = scope;
                this.isShow = true;
            },
        },
        created() {
            this.getList();
        },
        components: {
            OrderForm,
        }
    }
</script>

<style scoped>

</style>