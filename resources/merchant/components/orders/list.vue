<template>
    <page title="订单管理" v-loading="isLoading">
        <el-row align="bottom" type="flex">
            <el-col :span="22">
                <el-form :model="query" class="fl" inline size="small">
                    <el-form-item label="订单号">
                        <el-input v-model="query.orderNo" clearable placeholder="请输入订单号"/>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input v-model="query.notifyMobile" clearable placeholder="请输入手机号"/>
                    </el-form-item>
                    <el-form-item prop="createdAt" label="下单时间">
                        <el-date-picker
                                v-model="query.createdAt"
                                type="daterange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                value-format="yyyy-MM-dd">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="订单类型" prop="type">
                        <el-select v-model="query.type" size="small" clearable placeholder="请选择">
                            <el-option label="全部" value=""/>
                            <el-option label="团购" value="1"/>
                            <el-option label="买单" value="2"/>
                            <el-option label="单品" value="3"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="query.type == 1" label="商品名称">
                        <el-input v-model="query.goodsName" clearable placeholder="请输入商品名称"/>
                    </el-form-item>
                    <el-form-item label="订单状态" prop="status">
                        <el-select v-model="query.status" size="small"  multiple placeholder="全部">
                            <el-option label="未支付" value="1"/>
                            <!--<el-option label="已取消" value="2"/>-->
                            <el-option label="已关闭[超时自动关闭]" value="3"/>
                            <el-option label="已支付" value="4"/>
                            <!--<el-option label="退款中[保留状态]" value="5"/>-->
                            <el-option label="已退款" value="6"/>
                            <el-option label="已完成" value="7"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="search" type="primary"><i class="el-icon-search"></i> 搜索</el-button>
                        <el-button type="success" @click="exportExcel">导出Excel</el-button>
                    </el-form-item>
                    <el-form-item>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="2" class="m-b-20">
                <el-button type="primary" class="fr" @click="showItems">核销</el-button>
            </el-col>
        </el-row>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID" width="100px"/>
            <el-table-column prop="created_at" label="下单时间" width="150px"/>
            <el-table-column prop="order_no" label="订单号" width="300px"/>
            <el-table-column prop="type" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购</span>
                    <span v-else-if="scope.row.type == 2">买单</span>
                    <span v-else-if="scope.row.type == 3">单品</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="goods_name" label="商品名称" width="300px">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 3 && scope.row.dishes_items && scope.row.dishes_items.length == 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}
                    </span>
                    <span v-else-if="scope.row.type == 3 && scope.row.dishes_items && scope.row.dishes_items.length > 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}等{{getNumber(scope.row.dishes_items)}}件商品
                    </span>
                    <span v-else-if="scope.row.type == 2">
                        无
                    </span>
                    <span v-else>
                        {{scope.row.goods_name}}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_price" label="总价 ¥"/>
            <el-table-column prop="notify_mobile" label="手机号"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-warning">未支付</span>
                    <!--<span v-else-if="parseInt(scope.row.status) === 2" class="c-gray">已取消</span>-->
                    <span v-else-if="parseInt(scope.row.status) === 3" class="c-gray">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4" class="c-green">已支付</span>
                    <!--<span v-else-if="parseInt(scope.row.status) === 5" class="c-danger">退款中[保留状态]</span>-->
                    <span v-else-if="parseInt(scope.row.status) === 6" class="c-gray">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7" class="c-green">已完成</span>
                    <span v-else class="c-danger">未知 ({{scope.row.status}})</span>
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
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="showDetail(scope.row)">查看详情</el-button>
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
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import OrderForm from './form'

    export default {
        data() {
            return {
                isLoading: false,
                isShow: false,
                isShowItems: false,
                list: [],
                query: {
                    page: 1,
                    orderNo: '',
                    notifyMobile: '',
                    createdAt: '',
                    type: '',
                    status: '',
                    goodsName: '',
                    pay_type: '',
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
                let message = '确定导出全部财务列表么?'
                if(this.query.orderNo || this.query.notifyMobile || this.query.createdAt || this.query.type || this.query.status || this.query.goodsName){
                    message = '确定导出当前筛选的财务列表么?'
                }
                this.$confirm(message).then(() => {
                    window.open('/api/merchant/orders/export?orderNo=' + this.query.orderNo + '&notifyMobile=' + this.query.notifyMobile + '&createdAt=' + this.query.createdAt + '&type=' + this.query.type + '&status=' + this.query.status + '&goodsName=' + this.query.goodsName)
                })
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
            this.getList();
        },
        components: {
            OrderForm,
        }
    }
</script>

<style scoped>

</style>