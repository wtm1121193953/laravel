<template>
    <page title="订单管理" v-loading="isLoading">
        <el-form inline :model="query" size="small">
            <el-form-item label="订单号">
                <el-input type="text" clearable v-model="query.orderNo"/>
            </el-form-item>
            <el-form-item label="手机号">
                <el-input type="text" clearable v-model="query.mobile" class="w-150"/>
            </el-form-item>
            <el-form-item label="所属商户">
                <el-select v-model="query.merchantId" filterable clearable >
                    <el-option value="" label="全部"/>
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>

            <el-form-item label="订单类型">
                <el-select v-model="query.type" class="w-100" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="团购" :value="1"/>
                    <el-option label="买单" :value="2"/>
                    <el-option label="单品" :value="3"/>
                </el-select>
            </el-form-item>


            <el-form-item label="订单状态">
                <el-select v-model="query.status" class="w-100" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="待支付" :value="1"/>
                    <el-option label="已取消" :value="2"/>
                    <el-option label="已关闭[超时自动关闭]" :value="3"/>
                    <el-option label="已支付" :value="4"/>
                    <el-option label="已退款" :value="6"/>
                    <el-option label="已完成" :value="7"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-select v-model="query.timeType" class="w-150">
                    <el-option value="payTime" label="支付时间"/>
                    <el-option value="finishTime" label="核销时间"/>
                </el-select> :
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
            <el-form-item>
                <el-button type="primary" @click="search">
                    <i class="el-icon-search"></i> 搜索
                </el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" class="m-l-30" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column type="expand">
                <template slot-scope="scope">
                    <el-col :span="14">
                        <el-form label-position="left" inline class="table-expand">
                            <el-form-item label="创建时间">
                                <span>{{ scope.row.created_at }}</span>
                            </el-form-item>
                            <el-form-item label="支付时间" v-if="scope.row.pay_time">
                                <span>{{ scope.row.pay_time }}</span>
                            </el-form-item>
                            <el-form-item label="核销时间" v-if="scope.row.finish_time && scope.row.type != 2">
                                <span>{{ scope.row.finish_time }}</span>
                            </el-form-item>
                            <el-form-item label="手机号">
                                <span>{{ scope.row.notify_mobile }}</span>
                            </el-form-item>

                            <el-form-item label="商品信息：" v-if="scope.row.type == 3">
                                <div v-for="(item, index) in scope.row.dishes_items" :key="index">
                                    <span>{{item.dishes_goods_name}}</span>&nbsp;&nbsp;&nbsp;
                                    <span>¥{{item.dishes_goods_sale_price}}</span>&nbsp;&nbsp;&nbsp;
                                    <span>×{{item.number}}</span><br/>
                                </div>
                            </el-form-item>

                            <el-form-item label="备注">
                                <span>{{ scope.row.remark }}</span>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </template>
            </el-table-column>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="merchant_name" label="所属商户"/>
            <el-table-column prop="created_at" label="创建时间"/>
            <el-table-column prop="order_no" label="订单号"/>
            <el-table-column prop="type" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购</span>
                    <span v-else-if="scope.row.type == 2">买单</span>
                    <span v-else-if="scope.row.type == 3">单品</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="goods_name" label="商品名称">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 3 && scope.row.dishes_items.length == 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}
                    </span>
                    <span v-else-if="scope.row.type == 3 && scope.row.dishes_items.length > 1">
                        {{scope.row.dishes_items[0].dishes_goods_name}}等{{getNumber(scope.row.dishes_items)}}件商品
                    </span>
                    <span v-else>
                        {{scope.row.goods_name}}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_price" label="总价 ¥"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">未支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已取消</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4">已支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">退款中[保留状态]</span>
                    <span v-else-if="parseInt(scope.row.status) === 6">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7">已完成</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
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
        name: "merchant-list",
        data(){
            return {
                activeTab: 'merchant',
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
                    type: '',
                },
                list: [],
                total: 0,
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
                location.href = '/api/oper/order/export?' + array.join('&');
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
            }
        },
        created(){
            this.getList();
            this.getMerchants();
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