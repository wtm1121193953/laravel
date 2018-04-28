<template>
    <page title="商户池" v-loading="isLoading">
        <el-col>
            <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>
                <el-form-item>
                    <el-input v-model="query.keyword" placeholder="请输入关键字搜索" @keyup.native.enter="search"/>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="search">搜索</el-button>
                </el-form-item>
                <el-form-item label="" prop="isMine">
                    <el-checkbox v-model="query.isMine" true-label="1" false-label="0" @change="search">我录入的</el-checkbox>
                </el-form-item>
            </el-form>
            <el-button class="fr" type="primary" @click="add">录入商户信息</el-button>
        </el-col>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="商户ID" />
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="categoryPath" label="行业">
                <template slot-scope="scope">
                    <span v-for="item in scope.row.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <!--<span> {{ scope.row.province }} </span>-->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <merchant-pool-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @refresh="getList"/>
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

    import MerchantPoolItemOptions from './merchant-pool-item-options'
    import MerchantForm from './merchant-pool-form'

    export default {
        name: "merchant-list",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                    keyword: '',
                    isMine: '',
                },
                list: [],
                total: 0,
            }
        },
        computed: {
        },
        methods: {
            getList(){
                this.isLoading = true;
                api.get('/merchant/pool', this.query).then(data => {
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            search(){
                console.log(this.query)
                this.query.page = 1;
                this.getList();
            },
            itemChanged(index, data){
                this.getList();
            },
            add(){
                router.push({
                    path: '/merchant/pool/add',
                });
            },
            accountChanged(scope, account){
                let row = this.list[scope.$index];
                row.account = account;
                this.list.splice(scope.$index, 1, row);
                this.getList();
            },
        },
        created(){
            this.getList();
        },
        components: {
            MerchantPoolItemOptions,
            MerchantForm,
        }
    }
</script>

<style scoped>

</style>