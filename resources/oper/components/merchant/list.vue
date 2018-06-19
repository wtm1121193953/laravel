<template>
    <page title="我的商户" v-loading="isLoading">
        <el-tabs v-model="activeTab" type="card" @tab-click="changeTab">
            <el-tab-pane label="我的商户" name="merchant">
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
                    <el-table-column prop="status" label="商户状态">
                        <template slot-scope="scope" v-if="scope.row.audit_status == 1 || scope.row.audit_status == 3">
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
            </el-tab-pane>
            <el-tab-pane label="审核记录" name="audit">
                <audit-list ref="auditList"/>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import MerchantItemOptions from './merchant-item-options'
    import MerchantForm from './merchant-form'
    import AuditList from './audit-list'

    export default {
        name: "merchant-list",
        data(){
            return {
                activeTab: 'merchant',
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
            changeTab(tab){
                if(tab == 'merchant'){
                    this.getList();
                }else {
                    this.$refs.auditList.getList();
                }
            },
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
            AuditList,
        }
    }
</script>

<style scoped>

</style>