<template>
    <page title="商户管理" v-loading="isLoading">
        <el-dropdown class="fr" @command="addBtnClick" trigger="click">
            <el-button type="primary">
                添加商户<i class="el-icon-arrow-down el-icon--right"></i>
            </el-button>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="add">直接添加</el-dropdown-item>
                <el-dropdown-item command="from-pool">从商户池添加</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
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
            <el-table-column prop="status" label="商户状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.audit_status) === 0" class="c-warning">待审核</span>
                    <span v-else-if="parseInt(scope.row.audit_status) === 1" class="c-green">审核通过</span>
                    <span v-else-if="parseInt(scope.row.audit_status) === 2" class="c-danger">审核不通过</span>
                    <span v-else-if="parseInt(scope.row.audit_status) === 3" class="c-warning">重新提交审核中</span>
                    <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <merchant-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @accountChanged="accountChanged"
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

    import MerchantItemOptions from './merchant-item-options'
    import MerchantForm from './merchant-form'

    export default {
        name: "merchant-list",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
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
                api.get('/merchants', this.query).then(data => {
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
                router.push('/merchant/add');
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