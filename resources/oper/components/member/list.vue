<template>
    <page title="我的会员" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="用户手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="用户手机号码"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item label="渠道名称" prop="invite_channel_id">
                <el-select v-model="query.invite_channel_id" size="small"   placeholder="请选择" class="w-150">
                    <el-option value="0" label="全部"/>
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
        <el-table :data="list" stripe @sort-change="sortChange">
            <el-table-column prop="user_created_at" label="注册时间"/>
            <el-table-column prop="mobile" label="手机号"/>
            <<el-table-column prop="wx_nick_name" label="微信昵称"/>
            <el-table-column prop="invite_channel_name" label="渠道"/>
            <el-table-column prop="order_count" label="下单次数" sortable="custom"/>
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
                    invite_channel_id:'',
                    orderColumn: null,
                    orderType: null,
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
            sortChange (column) {
                this.query.orderColumn = column.prop;
                this.query.orderType = column.order;
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
                    let url = window.location.origin + '/api/oper/member/export?'
                        + 'mobile=' + this.query.mobile
                        + '&invite_channel_id=' + this.query.invite_channel_id ;
                    if (this.query.orderColumn && this.q.orderType) {
                        url += '&orderColumn=' + this.query.orderColumn ;
                        url += '&orderType=' + this.query.orderType ;
                    }
                    window.location.href = url ;
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