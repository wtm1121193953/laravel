<template>
    <page title="支付方式管理">
        <el-form class="fl" inline size="small">
            <el-form-item label="" prop="type">
                <el-select v-model="query.type" placeholder="支付方式类型">
                    <el-option label="全部" value=""/>
                    <el-option label="微信支付" value="1"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="name" label="">
                <el-input v-model="query.name" @keyup.enter.native="search" clearable placeholder="支付方式名称"/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr" type="primary" @click="add">添加支付方式</el-button>
        <el-table :data="list" stripe v-loading="isLoading">
            <el-table-column prop="id" label="ID" width="100px"/>
            <el-table-column prop="name" label="名称" />
            <el-table-column prop="type" label="类型">
            </el-table-column>
            <el-table-column prop="logo_url" label="logo">
                <template slot-scope="scope">
                    <img class="img" :src="scope.row.logo_url" width="100" height="100"/>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 2" class="c-warning">暂停</span>
                    <span v-else-if="scope.row.status === 2" class="c-green">启用</span>
                </template>
            </el-table-column>
            <el-table-column prop="on_pc" label="开放pc">
                <template slot-scope="scope">
                    <span v-if="scope.row.on_pc === 0" class="c-warning">否</span>
                    <span v-else-if="scope.row.on_pc === 1" class="c-green">是</span>
                </template>
            </el-table-column>
            <el-table-column prop="on_app" label="开放app">
                <template slot-scope="scope">
                    <span v-if="scope.row.on_app === 0" class="c-warning">否</span>
                    <span v-else-if="scope.row.on_app === 1" class="c-green">是</span>
                </template>
            </el-table-column>
            <el-table-column prop="on_miniprogram" label="开放小程序">
                <template slot-scope="scope">
                    <span v-if="scope.row.on_miniprogram === 0" class="c-warning">否</span>
                    <span v-else-if="scope.row.on_miniprogram === 1" class="c-green">是</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope)">修改</el-button>
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
        name: "payment-list",
        data(){
            return {
                isLoading: false,
                query: {
                    app_name: '',
                    app_type: ''
                },
                list: [],
                total: 0,
            }
        },
        computed: {

        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                api.get('/payments', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                router.push('/payment/add')
            },
            edit(scope){
                router.push({
                    path: '/payment/edit',
                    query: {id: scope.row.id}
                });
            },
        },
        created(){
            this.getList();
        },
        components: {
        }
    }
</script>

<style scoped>

</style>