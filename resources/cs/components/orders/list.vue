<template>
    <el-col v-loading="isLoading">
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
            <el-table-column prop="pay_time" width="150" label="支付时间"/>
            <el-table-column prop="order_no" width="200" label="订单号"/>
            <el-table-column prop="take_time" label="历时"/>
            <el-table-column prop="type" width="80" label="订单类型">
                <template slot-scope="scope">
                    <span>超市订单</span>
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
                            v-if="scope.row.deliver_type == 1"
                            placement="bottom"
                            trigger="hover"
                            width="250"
                    >
                        <div>
                            <div>快递公司：{{scope.row.express_company}}</div>
                            <div>快递公司：{{scope.row.express_no}}</div>
                        </div>
                        <span slot="reference">配送</span>
                    </el-popover>
                    <el-popover
                            v-else-if="scope.row.deliver_type == 2"
                            placement="bottom"
                            trigger="hover"
                            width="400"
                    >
                        <div>
                            <div>{{scope.row.express_company}}</div>
                            <div>{{scope.row.express_no}}</div>
                        </div>
                        <span slot="reference">自提</span>
                    </el-popover>
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
                :total="total"
        />

        <el-dialog title="订单详情" :visible.sync="isShow">
            <order-form :scope="order"/>
        </el-dialog>

        <el-dialog title="核销" :visible.sync="isShowItems" width="30%">
            <div>(仅支持一次核销订单全部消费码)</div>
            <el-row>
                <el-col :span="16">
                    <el-input placeholder="请输入消费码" @keyup.native.enter="verification" v-model="verify_code"/>
                </el-col>
                <el-col :span="7" :offset="1">
                    <el-button type="primary" ref="verifyInput" @click="verification">核销</el-button>
                </el-col>
                <div v-if="verify_success" class="fl">核销成功！<el-button type="text" @click="showDetail(order)">查看订单</el-button></div>
                <div v-if="verify_fail">核销失败！请检查消费码</div>
            </el-row>
        </el-dialog>
    </el-col>
</template>

<script>
    import api from '../../../assets/js/api'
    import OrderForm from './form'

    export default {
        props: {
            activeTab: {
                required: true,
                type: String,
            },
            status: {
                default: '',
            }
        },
        data() {
            return {
                isLoading: false,
                isShow: false,
                isShowItems: false,
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
                },
                total: 0,
                order: {},
                verify_code: '',
                verify_success: false,
                verify_fail: false,
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
                this.isShowItems = false;
            },
            showItems() {
                this.isShowItems = true;
                this.verify_success = false;
                this.verify_fail = false;
                this.verify_code = '';
                setTimeout(() => {
                    this.$refs.verifyInput.focus();
                }, 1000)
            },
            verification(){
                this.verify_success = false;
                this.verify_fail = false;
                if (!this.verify_code){
                    this.$message.error('请填写消费码');
                    return false;
                }
                api.post('/verification', {verify_code: this.verify_code}, false).then(result => {
                    console.log(result);
                    if(result && parseInt(result.code) === 0){
                        this.order = result.data;
                        console.log('order',this.order);
                        this.verify_success = true;
                    }else{
                        this.verify_code = '';
                        this.verify_fail = true;
                        this.$message.error(result.message);
                    }
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
        created() {
            this.query.status = this.status;

            this.getList();
        },
        components: {
            OrderForm,
        }
    }
</script>

<style scoped>

</style>