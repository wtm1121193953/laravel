<template>
    <page :title="title" :breadcrumbs="{渠道换绑: '/member/changBind'}">
        <el-table stripe :data="list" v-loading="tableLoading">
            <el-table-column prop="user.id" label="用户ID"/>
            <el-table-column prop="created_at" label="绑定时间"/>
            <el-table-column prop="user.created_at" label="注册时间"/>
            <el-table-column prop="user.mobile" label="手机号"/>
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
    import api from '../../../../assets/js/api'
    export default {
        name: "invite-record-list",
        data() {
            return {
                inviteChannelId: '',
                inviteChannelName: '',
                list: [],
                total: 0,
                query: {
                    page: 1,
                },
                tableLoading: false,
            }
        },
        computed: {
            title() {
                return `注册人数详情 ( ${this.inviteChannelName}  )`
            }
        },
        methods: {
            getList(){
                this.tableLoading = true;
                this.query.id = this.inviteChannelId
                api.get('users/getInviteUsersList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
        },
        created(){
            this.inviteChannelId = this.$route.query.id;
            this.inviteChannelName = this.$route.query.name;
            if(!this.inviteChannelId){
                router.go(-1)
                return ;
            }
            this.getList()
        }
    }
</script>

<style scoped>

</style>