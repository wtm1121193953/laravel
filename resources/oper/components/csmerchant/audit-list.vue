<template>
    <page title="超市商户审核记录">
        <el-table :data="list" stripe v-loading="tableLoading">
            <el-table-column prop="created_at" label="提交审核时间"/>
            <el-table-column prop="audit_time" label="审核时间"/>
            <el-table-column prop="type" label="操作">
                <template slot-scope="scope">
                    <span>{{scope.row.type == 1 ? '新增商户' : '修改商户'}}</span>
                    <div v-if="scope.row.cs_merchant_id > 0" class="c-green">
                        商户ID: {{ scope.row.cs_merchant_id}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-warning">待审核</span>
                    <span v-else-if="scope.row.status === 2" class="c-green">审核通过</span>
                    <span v-else-if="scope.row.status === 3" class="c-danger">审核不通过</span>
                    <span v-else-if="scope.row.status === 4" class="c-danger">撤回审核</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <!--v-if="parseInt(scope.row.status)!==2"-->
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
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
                    page: 1,
                },
                list: [],
                total: 0,
                tableLoading: false
            }
        },
        methods: {
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
            edit(scope){
                router.push({
                    path: '/cs/merchant/edit',
                    query: {id: scope.row.id,type: 'cs-merchant-reedit'},
                })
                return false;
            },
            getList(){
                this.tableLoading = true;
                api.get('/cs/merchant/audit/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
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