<template>
    <page title="订单管理" v-loading="isLoading">
        <el-col>
            <el-button type="primary" class="fr" @click="showItems">核销</el-button>
        </el-col>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="created_at" label="创建时间"/>
            <el-table-column prop="order_no" label="订单号"/>
            <el-table-column prop="goods_name" label="商品名称"/>
            <el-table-column prop="pay_price" label="总价"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">未支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已取消</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4">已支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">退款中[保留状态]</span>
                    <span v-else-if="parseInt(scope.row.status) === 6">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7">已完成[不可退款]</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
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
                    <el-input placeholder="请输入消费码" @keyup.enter="verification" v-model="verify_code"/>
                </el-col>
                <el-col :span="7" :offset="1">
                    <el-button type="primary" ref="verifyInput" @click="verification">核销</el-button>
                </el-col>
                <div v-if="verify_success">核销成功！<el-button type="text" @click="showDetail(order)">查看订单</el-button></div>
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
                },
                total: 0,
                order: {},
                verify_code: '',
                verify_success: false,
                verify_fail: false,
            }
        },
        methods: {
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
                }, 300)
            },
            verification(){
                this.verify_success = false;
                this.verify_fail = false;
                if (!this.verify_code){
                    this.$message.error('请填写核销码');
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