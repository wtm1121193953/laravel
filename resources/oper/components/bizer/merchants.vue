<template>
    <page :title="'业务-' + operBizMember.name" :breadcrumbs="{我的业务员: '/operBizMembers'}" v-loading="isLoading">
        <el-table :data="list" stripe>
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
                query: {
                    page: 1,
                },
                list: [
                    {
                        created_at: '2018',
                        audit_done_time: '2018',
                        name: '一个名词',
                        status: 1,
                    }
                ],
                total: 0,
                operBizMember: {},
            }
        },
        methods: {
            getList(){
                this.query.code = this.operBizMember.code;
                api.get('/operBizMember/merchants', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
        },
        created(){
            let id = this.$route.query.id;
            if(!id){
                this.$message.warning('id不能为空');
                router.go(-1);
                return ;
            }
            api.get('/operBizMembers/detail', {id: id}).then(data => {
                this.operBizMember = data;
                this.getList();
            });
        },
    }
</script>

<style scoped>

</style>