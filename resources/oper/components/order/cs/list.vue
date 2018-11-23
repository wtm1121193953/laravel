<template>
    <el-col v-loading="isLoading">
        <el-form inline :model="query" size="small">
            <el-form-item label="订单号">
                <el-input type="text" clearable v-model="query.orderNo" placeholder="输入订单号"/>
            </el-form-item>
            <el-form-item label="手机号">
                <el-input type="text" clearable v-model="query.mobile" placeholder="输入手机号" class="w-150"/>
            </el-form-item>
            <el-form-item label="所属商户">
                <el-select v-model="query.merchantId" placeholder="输入商户ID或商户名" filterable clearable >
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>

            <el-form-item label="订单状态" v-if="activeTab == 'all'">
                <el-select v-model="query.status" class="w-100" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="待发货" value="8"/>
                    <el-option label="待自提" value="9"/>
                    <el-option label="已发货" value="10"/>
                    <el-option label="已完成" value="7"/>
                    <el-option label="已退款" value="6"/>
                </el-select>
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
        <el-table :data="list" stripe>
            <el-table-column prop="pay_time" width="150" label="支付时间"/>
            <el-table-column prop="order_no" width="200" label="订单号"/>
            <el-table-column prop="take_time" label="历时"/>
            <el-table-column prop="type" width="80" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购订单</span>
                    <span v-else-if="scope.row.type == 2">扫码支付订单</span>
                    <span v-else-if="scope.row.type == 3">点菜订单</span>
                    <span v-else-if="scope.row.type == 4">超市订单</span>
                    <span>其他({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="goods_name" width="250" label="商品名称">
                <template slot-scope="scope">
                    <span v-if="scope.row.cs_order_goods.length == 1">
                        {{scope.row.cs_order_goods[0].goods_name}}
                    </span>
                    <span v-else-if="scope.row.cs_order_goods.length > 1">
                        {{scope.row.cs_order_goods[0].goods_name}}等{{getNumber(scope.row.cs_order_goods)}}件商品
                    </span>
                    <span v-else>
                        无
                    </span>
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
            <el-table-column prop="deliver_type" label="发货方式">
                <template slot-scope="scope">
                    <el-popover
                        v-if="scope.row.deliver_type == 1 && scope.row.status != 8"
                        placement="bottom"
                        trigger="hover"
                        width="250"
                    >
                        <div>
                            <div>快递公司：{{scope.row.express_company}}</div>
                            <div>快递单号：{{scope.row.express_no}}</div>
                        </div>
                        <span slot="reference">配送</span>
                    </el-popover>
                    <span v-else-if="scope.row.deliver_type == 1">配送</span>
                    <span v-else-if="scope.row.deliver_type == 2">自提</span>
                    <span v-else>无</span>
                </template>
            </el-table-column>
            <el-table-column prop="merchant_name" width="250" label="商户名称"/>
            <el-table-column label="操作">
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
                :total="total"/>


        <el-dialog title="订单详情" :visible.sync="isShow">
            <order-form :scope="order"/>
        </el-dialog>


    </el-col>
</template>

<script>
    import api from '../../../../assets/js/api'
    import OrderForm from './form'

    export default {
        name: "merchant-list",
        props: {
            activeTab: {
                required: true,
                type: String,
            },
            status: {
                default: '',
            }
        },
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    orderNo: '',
                    mobile: '',
                    merchantId: '',
                    timeType: 'payTime',
                    startTime: '',
                    endTime: '',
                    status: '',
                    merchantType: 2,
                },
                list: [],
                total: 0,
                merchants: [],

                order: {},
                isShow: false,
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
                location.href = '/api/oper/order/cs/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                let queryData = deepCopy(this.query);
                api.get('/orders', queryData).then(data => {
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            getMerchants(){
                api.get('/merchant/allNames').then(data => {
                    this.merchants = data.list;
                })
            },
            getNumber(row) {
                let num = 0;
                row.forEach(function (item) {
                    num = num + item.number;
                })
                return num;
            },
            showDetail(scope) {
                this.order = scope;
                this.isShow = true;
            },
        },
        created(){
            this.query.status = this.status;

            this.getList();
            this.getMerchants();
        },
        components: {
            OrderForm,
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