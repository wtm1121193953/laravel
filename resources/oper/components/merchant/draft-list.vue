<template>
    <page title="草稿箱" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item label="" prop="name">
                <el-input v-model="query.name" @keyup.enter.native="search" placeholder="商户名称"/>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status">
                    <el-option label="全部" value=""/>
                    <el-option label="正常" value="1"/>
                    <el-option label="已冻结" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item label="审核状态" prop="audit_status">
                <el-select v-model="query.audit_status" placeholder="请选择">
                    <el-option label="全部" value=""/>
                    <el-option label="待审核" value="-1"/>
                    <el-option label="审核通过" value="1"/>
                    <el-option label="审核不通过" value="2"/>
                    <el-option label="重新提交审核" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <!--<el-dropdown class="fr" @command="addBtnClick" trigger="click">
            <el-button type="primary">
                添加商户<i class="el-icon-arrow-down el-icon&#45;&#45;right"></i>
            </el-button>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="from-pool">从商户池添加</el-dropdown-item>
                <el-dropdown-item command="add">添加新商户</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>-->
        <el-button class="fr" type="primary" @click="add">录入并激活商户</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="ID"/>
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
            <el-table-column prop="operBizMemberName" label="业务员"/>
            <el-table-column label="状态">
                <template>
                    暂存
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope.row)">编辑</el-button>
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

    import MerchantItemOptions from './merchant-item-options'
    import MerchantForm from './merchant-form'

    export default {
        name: "merchant-list",
        data(){
            return {
                isLoading: false,
                query: {
                    name: '',
                    status: '',
                    audit_status: '',
                    page: 1,
                },
                list: [],
                total: 0,
            }
        },
        computed: {

        },
        methods: {
            edit(row){
                router.push({
                    path: '/merchant/edit',
                    query: {
                        id: row.id,
                        type: 'draft-list',
                    }
                })
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                api.get('/merchant/drafts', this.query).then(data => {
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.getList();
            },
            addBtnClick(command){
                if(command === 'add'){
                    this.add()
                }else {
                    this.$menu.change('/merchant/pool')
                }
            },
            add(){
                router.push({
                    path: '/merchant/add',
                    query: {
                        type: 'draft-list'
                    }
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
            MerchantItemOptions,
            MerchantForm,
        }
    }
</script>

<style scoped>

</style>