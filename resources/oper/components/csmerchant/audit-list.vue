<template>
    <page title="超市商户审核记录">
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户ID">
                    <el-input v-model="query.merchantId" size="small" placeholder="商户ID" class="w-100" clearable></el-input>
                </el-form-item>
                <el-form-item prop="name" label="商户名称">
                    <el-input v-model="query.name" size="small" placeholder="商户名称" clearable
                              @keyup.enter.native="search"/>
                </el-form-item>
                <el-form-item label="审核状态" prop="status">
                    <el-select v-model="query.status" size="small" multiple placeholder="请选择">
                        <el-option label="待审核" value="1"/>
                        <el-option label="审核通过" value="2"/>
                        <el-option label="审核不通过" value="3"/>
                        <el-option label="已撤回" value="4"/>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-table :data="list" stripe v-loading="tableLoading">
            <el-table-column prop="created_at" label="添加时间" width="160px"/>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="cs_merchant_id" width="160px" label="操作类型">
                <template slot-scope="scope">
                    <span>{{scope.row.type == 1 ? '新增商户' : '修改商户'}}</span>
                    <div v-if="scope.row.cs_merchant_id > 0" class="c-green">
                        商户ID: {{ scope.row.cs_merchant_id}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="name" label="商户类型">
                <template slot-scope="scope">
                    <span>超市类</span>
                </template>
            </el-table-column>
            <el-table-column prop="data_after.signboard_name" label="商户招牌名"/>
            <el-table-column prop="oper_id" size="mini" label="运营中心ID"/>
            <el-table-column prop="operName" label="运营中心名称"/>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <span> {{ scope.row.data_after.city }} </span>
                    <span> {{ scope.row.data_after.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-warning">待审核</span>
                    <el-popover
                            v-else-if="scope.row.status === 2"
                            placement="bottom-start"
                            width="200px" trigger="hover"
                            @show="showMessage(scope)">
                        <div slot="reference" class="c-green"><p>审核通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.suggestion || '无'}}
                        <!--<unaudit-record-reason :data="auditRecord"/>-->
                    </el-popover>
                    <el-popover
                            v-else-if="scope.row.status === 3"
                            placement="bottom-start"
                            width="200px" trigger="hover"
                            @show="showMessage(scope)">
                        <div slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.suggestion || '无'}}
                        <!--<unaudit-record-reason :data="auditRecord"/>-->
                    </el-popover>
                    <span v-else-if="scope.row.status == 4" class="c-gray">已撤回</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="cs_merchant_detail.settlement_cycle_type" label="结算周期">
                <template slot-scope="scope">
                    <span>{{ {1: '周结', 2: '半月结', 3: 'T+1(自动)', 4: '半年结', 5: '年结', 6: 'T+1(人工)', 7: '未知',}[scope.row.data_after.settlement_cycle_type] }}</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button  type="text" @click="showMearchant(scope)">查看</el-button>
                    <el-button v-if="parseInt(scope.row.status) === 1" type="text" @click="recall(scope)">撤回审核</el-button>
                    <el-button v-if="parseInt(scope.row.status) > 2" type="text" @click="edit(scope)">重新编辑</el-button>
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
        name: "audit-list",
        data() {
            return {
                query: {
                    merchantId: '',
                    name: '',
                    status: '',
                    page: 1,
                },
                list: [],
                total: 0,
                tableLoading: false
            }
        },
        methods: {
            search() {
                this.query.page = 1;
                this.getList();
            },
            recall(scope){

                this.$confirm('确定撤回吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    api.post('/cs/merchant/recall', {id: scope.row.id,type: 'merchant-list'}).then(data => {
                        this.$message.success('撤回成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            showMearchant(scope){
                router.push({
                    path: '/cs/merchant/detail',
                    query: {id: scope.row.id,type: 'cs-merchant-reedit'},
                })
                return false;

            },
            edit(scope){
                router.push({
                    path: '/cs/merchant/edit',
                    query: {id: scope.row.id,type: 'cs-merchant-reedit'},
                })
                return false;
            },
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/cs/merchant/audit/list', params).then(data => {
                    console.log(data.list);
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            }
        },
        created(){
            this.getList();
        }
    }
</script>

<style scoped>

</style>