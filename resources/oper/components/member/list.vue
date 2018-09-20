<template>
    <page title="我的会员" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="用户手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="用户手机号码"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item label="渠道名称" prop="invite_channel_id">
                <el-select v-model="query.invite_channel_id" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option value="" label="全部"/>
                    <el-option v-for="item in channels" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="success" size="small" @click="downloadExcel">导出</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column prop="user.created_at" label="注册时间"/>
            <el-table-column prop="user.mobile" label="手机号"/>
            <<el-table-column prop="user.wx_nick_name" label="微信昵称"/>
            <el-table-column prop="invite_channel_name" label="渠道"/>
            <el-table-column prop="user.order_count" label="下单次数"/>
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
        name: "member-list",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                    mobile: '',
                    invite_channel_id:0
                },
                list: [],
                total: 0,
                currentMerchant: null,
                channels:[],
            }
        },
        computed: {

        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                api.get('/member/userlist', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
                this.getList();
            },
            downloadExcel() {
                let message = '确定要导出当前筛选的用户列表么？'
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/oper/member/export?'
                        + 'mobile=' + this.query.mobile
                        + '&invite_channel_id=' + this.query.invite_channel_id ;
                })
            },
            getChannels() {
                api.get('/member/channels').then(data => {
                    this.channels = data.list;
                })
            }
        },
        created(){
            this.getList();
            this.getChannels();
        },
        components: {
        }
    }
</script>

<style scoped>

</style>