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
                <el-input v-model="query.id" placeholder="请输入商户ID" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="merchantName" label="商户名称">
                <el-input v-model="query.merchantName" placeholder="请输入商户名称" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="merchant_category" label="行业">
                <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="categoryOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.merchant_category">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="cityId" label="所在城市">
                <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="areaOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.cityId">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="operId" label="所属运营中心">
                    <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="operOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.operId">
                    </el-cascader>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="商户ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="signboard_name" label="商户招牌名"/>
            <el-table-column prop="categoryPath" label="行业">
                <template slot-scope="scope">
                    <span v-for="item in scope.row.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="city" label="所在城市">
                <template slot-scope="scope">
                    <!-- <span> {{ scope.row.province }} </span> -->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="operName" label="所属运营中心"/>
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
                    startTime: '',
                    endTime: '',
                    id:'',
                    merchantName: '',
                    merchant_category:'',
                    cityId :'',
                    operId :'',
                    page: 1
                },
                list: [],
                total: 0
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
                let params = {};
                Object.assign(params, this.query);
                api.get('/merchants', params).then(data => {
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.getList();
            },
            showMessage(scope){
                api.get('/merchant/audit/record/newest', {id: scope.row.id}).then(data => {
                    this.auditRecord = [data];
                })
            },
            
            accountChanged(scope, account){
                let row = this.list[scope.$index];
                row.account = account;
                this.list.splice(scope.$index, 1, row);
                this.getList();
            },
        },
        created(){
            api.get('area/tree').then(data => {
                this.areaOptions = data.list;
            });
            this.areaOptions = [];
            
            api.get('merchant/opers/tree').then(data => {
                this.operOptions = data.list;
            });
            this.operOptions = [];
            api.get('merchant/categories/tree').then(data => {
                 this.categoryOptions = data.list;
             });
             this.categoryOptions = [];
             if (this.$route.params){
                 Object.assign(this.query, this.$route.params);
             }
             this.getList();
        },
        components: {

        }
    }
</script>

<style scoped>

</style>
