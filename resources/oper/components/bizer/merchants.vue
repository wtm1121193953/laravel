<template>
    <page :title="'业务-' + operBizMember.name + '-' + operBizMember.mobile" :breadcrumbs="{我的业务员: '/operBizMembers'}" v-loading="isLoading">
        <el-table :data="list" stripe v-loading="tableLoading">
            <el-table-column prop="created_at" label="添加商户时间"/>
            <el-table-column prop="audit_done_time" label="商户审核通过时间"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="status" label="商户状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                    <span v-if="parseInt(scope.row.is_pilot) === 1">(试点商户)</span>
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
    export default {
        // name: "oper-biz-member-merchants",
        data() {
            return {
                isLoading: false,
                tableLoading: false,
                query: {
                    page: 1,
                },
                list: [
                ],
                total: 0,
                operBizMember: {
                    name:'',
                    mobile: '',
                },
            }
        },
        methods: {
            getList(){
                let _self = this;
                _self.query.code = _self.operBizMember.code;
                _self.tableLoading = true;
                api.get('/operBizMember/merchants', _self.query).then(data => {
                    _self.list = data.list;
                    _self.total = data.total;
                }).finally(() => {
                    _self.tableLoading = false;
                })
            },
        },
        created(){
            let _self = this;
            let bizer_id = _self.$route.query.bizer_id;
            if(!bizer_id){
                _self.$message.warning('无效用户');
                router.go(-1);
                return ;
            }
            _self.isLoading = true;
            api.get('/operBizMembers/detail', {id: bizer_id}).then(data => {
                _self.operBizMember = data;
                _self.getList();
            }).finally(() => {
                _self.isLoading = false;
            });
        },
    }
</script>

<style scoped>

</style>