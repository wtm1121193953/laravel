<template>
    <page title="换绑人列表" :breadcrumbs="{渠道换绑: '/member/changBind'}">
        <el-table stripe :data="list" v-loading="tableLoading">
            <el-table-column prop="user_id" label="用户ID"/>
            <el-table-column prop="mobile" label="用户手机号码"/>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="query.pageSize"
                :total="total"/>

    </page>
</template>

<script>
    import api from '../../../../assets/js/api'
    export default {
        name: "change-bind-record-list",
        data() {
            return {
                changeBindRecordId: '',
                list: [],
                total: 0,
                query: {
                    page: 1,
                    pageSize: 15,
                },
                tableLoading: false,
            }
        },
        methods: {
            getList(){
                this.tableLoading = true;
                this.query.id = this.changeBindRecordId;
                api.get('users/getChangeBindPeopleRecordList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
        },
        created(){
            this.changeBindRecordId = this.$route.query.id;
            if(!this.changeBindRecordId){
                router.go(-1)
                return ;
            }
            this.getList()
        }
    }
</script>

<style scoped>

</style>