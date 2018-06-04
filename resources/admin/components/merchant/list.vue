<template>
    <page title="商户审核管理" v-loading="isLoading">
        <el-tabs v-model="activeTab" type="card" @tab-click="changeTab">
            <el-tab-pane label="待审核商户列表" name="merchant">

                <el-form class="fl" inline size="small">
                    <el-form-item label="商户名称" prop="name">
                        <el-input v-model="query.name" placeholder="商户名称" @keyup.enter.native="search"/>
                    </el-form-item>
                    <el-form-item label="状态" prop="status">
                        <el-select v-model="query.status" placeholder="请选择">
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

                <el-table :data="list" stripe>
                    <el-table-column prop="created_at" label="添加时间"/>
                    <el-table-column prop="id" label="商户ID"/>
                    <el-table-column prop="operName" label="激活运营中心"/>
                    <el-table-column prop="creatorOperName" label="录入运营中心"/>
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
                    <el-table-column prop="audit_status" label="审核状态">
                        <template slot-scope="scope">
                            <span v-if="scope.row.audit_status === 0" class="c-warning">待审核</span>
                            <span v-else-if="scope.row.audit_status === 1" class="c-green">审核通过</span>
                            <span v-else-if="scope.row.audit_status === 2" class="c-danger">审核不通过</span>
                            <span v-else-if="scope.row.audit_status === 3" class="c-warning">待审核(重新提交)</span>
                            <span v-else>未知 ({{scope.row.audit_status}})</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="150px">
                        <template slot-scope="scope">
                            <el-button type="text" @click="detail(scope)">查看</el-button>
                            <template v-if="scope.row.audit_status === 0 || scope.row.audit_status === 3">

                                <el-dropdown trigger="click"
                                             style="margin-left: 10px;"
                                             @command="(command) => {audit(scope, command)}">
                                    <el-button type="text">
                                      审核商户 <i class="el-icon-arrow-down"></i>
                                    </el-button>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item command="1">审核通过</el-dropdown-item>
                                        <el-dropdown-item command="2">审核不通过</el-dropdown-item>
                                        <el-dropdown-item
                                                v-if="scope.row.oper_id == 0"
                                                command="3">打回到商户池</el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                                <!--<el-button type="text" @click="audit(scope, 1)">审核通过</el-button>-->
                                <!--<el-button type="text" @click="audit(scope, 2)">审核不通过</el-button>-->
                                <!--<el-button v-if="scope.row.oper_id == 0" type="text" @click="audit(scope, 3)">打回到商户池</el-button>-->
                            </template>
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
        <el-dialog :visible.sync="showDetail" width="70%" title="商户详情">
            <merchant-detail :data="currentMerchant" @change="() => {getList(); showDetail = false;}"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantDetail from './merchant-detail'
    import AuditList from './audit-list'

    export default {
        name: "merchant-list",
        data(){
            return {
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                query: {
                    name: '',
                    status: '',
                    page: 1,
                },
                list: [],
                total: 0,
                currentMerchant: null,
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
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                api.get('/merchants', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            detail(scope){
                router.push({
                    path: '/merchant/detail',
                    query: {id: scope.row.id},
                })
                return false;
            },
            audit(scope, type){
                //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
                let message = ['', '审核通过', '审核不通过', '打回到商户池'][type];
                this.$confirm(`确定 ${message} 吗?`, scope.row.name).then(() => {
                    api.post('/merchant/audit', {id: scope.row.id, type: type}).then(data => {
                        this.$alert(message + ' 操作成功');
                        this.getList();
                    })
                });
            }
        },
        created(){
            this.getList();
        },
        components: {
            MerchantDetail,
            AuditList,
        }
    }
</script>

<style scoped>

</style>