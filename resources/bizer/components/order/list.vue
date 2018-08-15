<template>
    <page title="订单列表" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="createdAt" label="创建时间">
                <el-date-picker
                        v-model="query.createdAt"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
            </el-form-item>
            <el-form-item prop="id" label="订单号">
                <el-input v-model="query.name" placeholder="请输入订单号" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="type" label="订单类型">
                <el-select v-model="query.type" class="w-100">
                    <el-option label="全部" value=""/>
                    <el-option label="团购订单" value="1"/>
                    <el-option label="扫码买单" value="2"/>
                    <el-option label="单品订单" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="name" label="商品名称">
                <el-input v-model="query.name" placeholder="请输入商品名称" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="businessName" label="商户名称">
                <el-select v-model="query.businessName">
                    <el-option label="全部" value=""/>
                    <el-option label="团购订单" value="1"/>
                    <el-option label="扫码买单" value="2"/>
                    <el-option label="单品订单" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="own" label="所属运营中心">
                <el-select v-model="query.own">
                    <el-option label="全部" value=""/>
                    <el-option label="大千生活华南地区运营中心" value="1"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="list" stripe>
            <el-table-column prop="date" label="创建时间"/>
            <el-table-column prop="id" label="订单号"/>
            <el-table-column prop="type" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type === 1">团购订单</span>
                    <span v-else-if="scope.row.type === 2">扫码买单</span>
                    <span v-else-if="scope.row.type === 3">单品订单</span>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="商品名称"/>
            <el-table-column prop="price" label="总价（元）"/>
            <el-table-column prop="tel" label="手机号"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1">已消费</span>
                    <span v-else-if="scope.row.status === 2">已支付</span>
                    <span v-else-if="scope.row.status === 3">已退款</span>
                    <span v-else-if="scope.row.status === 4">已完成</span>
                </template>
            </el-table-column>
            <el-table-column prop="business_name" label="商户名称"/>
            <el-table-column prop="own" label="所属运营中心"/>            
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="details()">查看详情</el-button>
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

        <el-dialog title="订单详情" :visible.sync="dialogDetailVisible" width="40%">
            <div class="dialog-details clearfix">
                <dl>
                    <dd class="c-danger">订单类型：团购订单</dd>
                    <dd>商户名称：我是宇宙牛逼店铺</dd>
                    <dd>单价：100元</dd>
                    <dd>总价：200</dd>
                    <dd>身份：萌新</dd>
                    <dd>订单状态：已消费</dd>
                </dl>
                <dl>
                    <dd>订单号：45675678567</dd>
                    <dd>商品名称：我是宇宙牛逼店铺的商品名</dd>
                    <dd>数量：2</dd>
                    <dd>手机号：159****2307</dd>
                    <dd>返利积分：20</dd>
                    <dd>订单创建时间：2018-07-05 12:30:20</dd>
                    <!-- <dd>
                        <p>商品信息：</p>
                        <p class="clearfix"><span class="fl">我是宇宙牛逼店铺的商品名</span><span class="fr">100¥</span></p>
                        <p class="clearfix"><span class="fl">我是宇宙牛逼店铺的商品名</span><span class="fr">100¥</span></p>
                        <p class="clearfix"><span class="fl">我是宇宙牛逼店铺的商品名</span><span class="fr">100¥</span></p>
                    </dd> -->
                </dl>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogDetailVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogDetailVisible = false">发送申请</el-button>
            </div>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data(){
            return {
                isLoading: false,
                query: {
                    createdAt: '',
                    id: '',
                    type: '',
                    name: '',
                    businessName: '',
                    own: '',
                    page: 1
                },
                // list: [],
                list: [{
                    date: '2018-07-05 12:30:20',
                    id: '564565465344',
                    type: 1,
                    name: '榴莲蛋糕',
                    price: '100.00',
                    tel: '139********3321',
                    status: 1,
                    business_name: '小哥哥椰子鸡',
                    own: '大千生活华南地区运营中心'
                }, {
                    date: '2018-07-05 12:30:20',
                    id: '564565465344',
                    type: 2,
                    name: '榴莲蛋糕',
                    price: '100.00',
                    tel: '139********3321',
                    status: 2,
                    business_name: '小哥哥椰子鸡',
                    own: '大千生活华南地区运营中心'
                }, {
                    date: '2018-07-05 12:30:20',
                    id: '564565465344',
                    type: 3,
                    name: '榴莲蛋糕',
                    price: '100.00',
                    tel: '139********3321',
                    status: 3,
                    business_name: '小哥哥椰子鸡',
                    own: '大千生活华南地区运营中心'
                }, {
                    date: '2018-07-05 12:30:20',
                    id: '564565465344',
                    type: 1,
                    name: '榴莲蛋糕',
                    price: '100.00',
                    tel: '139********3321',
                    status: 4,
                    business_name: '小哥哥椰子鸡',
                    own: '大千生活华南地区运营中心'
                }],
                total: 0,

                dialogDetailVisible: false
            }
        },
        computed: {

        },
        methods: {
            search(){
                // this.query.page = 1;
                // this.getList();
            },
            getList(){
                // this.isLoading = true;
                // let params = {};
                // Object.assign(params, this.query);
                // api.get('/merchants', params).then(data => {
                //     this.query.page = params.page;
                //     this.isLoading = false;
                //     this.list = data.list;
                //     this.total = data.total;
                // })
            },
            details(){
                this.dialogDetailVisible = true;
            },
        },
        created(){
            // api.get('merchant/categories/tree').then(data => {
            //     this.categoryOptions = data.list;
            // });
            // this.categoryOptions = [];
            // if (this.$route.params){
            //     Object.assign(this.query, this.$route.params);
            // }
            // this.getList();
        },
        components: {

        }
    }
</script>

<style scoped>
.clearfix::after {
    display: block;
    clear: both;
    content: "";
    visibility: hidden;
    height: 0;
}
.clearfix {
    zoom: 1;
}
.dialog-details {

}
.dialog-details dl {
    float: left;
    margin: 0;
    width: 50%;
    padding: 0 40px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
.dialog-details dl + dl {
    border-left: 1px solid #ebeef5;
}
.dialog-details dl dd {
    margin: 0;
    line-height: 24px;
    padding: 5px 0;
}
.dialog-details dl dd p {
    margin: 0;
}
</style>
