<template>
    <page title="商户列表" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="createdAt" label="添加时间">
                <el-date-picker
                        v-model="query.createdAt"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
            </el-form-item>
            <el-form-item prop="id" label="商户ID">
                <el-input v-model="query.principal" placeholder="请输入商户ID" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="name" label="商户名称">
                <el-input v-model="query.name" placeholder="请输入商户名称" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="industry" label="行业">
                <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="industryOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.industry">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="city" label="所在城市">
                <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="cityOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.city">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="own" label="所属运营中心">
                <el-select v-model="query.own">
                    <el-option label="全部" value=""/>
                    <el-option label="大千生活深圳运营中心" value="1"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="list" stripe>
            <el-table-column prop="date" label="添加时间"/>
            <el-table-column prop="id" label="商户ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="signboard_name" label="商户招牌名"/>
            <el-table-column prop="industry" label="行业"/>
            <el-table-column prop="city" label="所在城市">
                <template slot-scope="scope">
                    <!-- <span> {{ scope.row.province }} </span> -->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="own" label="所属运营中心"/>
            <el-table-column prop="divided_into" label="分成"/>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button type="text">查看订单</el-button>
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
        data(){
            return {
                isLoading: false,
                industryOptions: [],
                cityOptions: [],
                query: {
                    createdAt: '',
                    id: '',
                    name: '',
                    industry: '',
                    own: '',
                    page: 1
                },
                // list: [],
                list: [{
                    date: '2018-07-05 12:30:20',
                    id: '34354354564654',
                    name: '小哥哥混沌',
                    signboard_name: '小哥哥混沌',
                    industry: '美食',
                    own: '大千生活深圳运营中心',
                    divided_into: '20%'
                }, {
                    date: '2018-07-05 12:30:20',
                    id: '34354354564654',
                    name: '小哥哥混沌',
                    signboard_name: '小哥哥混沌',
                    industry: '美食',
                    own: '大千生活深圳运营中心',
                    divided_into: '20%'
                }, {
                    date: '2018-07-05 12:30:20',
                    id: '34354354564654',
                    name: '小哥哥混沌',
                    signboard_name: '小哥哥混沌',
                    industry: '美食',
                    own: '大千生活深圳运营中心',
                    divided_into: '20%'
                }],
                total: 0
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

</style>
