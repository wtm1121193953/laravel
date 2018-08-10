<template>
    <page :title="title" :breadcrumbs="{推广渠道: '/invite-channel'}">
        <el-form inline class="fl" :model="query" size="small">
            <el-form-item label="注册时间">
                <el-date-picker
                        v-model="query.startTime"
                        type="date"
                        placeholder="起始时间"
                        value-format="yyyy-MM-dd 00:00:00"
                        :picker-options="{disabledDate(time) {
                            return time.getTime() > (query.endTime ? new Date(query.endTime) : Date.now());
                        }}"
                ></el-date-picker>
                --
                <el-date-picker
                        v-model="query.endTime"
                        type="date"
                        placeholder="截止时间"
                        value-format="yyyy-MM-dd 23:59:59"
                        :picker-options="{disabledDate(time) {
                            return time.getTime() > Date.now();
                        }}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item prop="mobile" label="手机号">
                <el-input v-model="query.mobile" placeholder="请输入手机号" @keyup.enter.native="search" clearable></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
                <el-button type="success" @click="exportExcel"> 导出Excel </el-button>
            </el-form-item>
        </el-form>

        <el-table stripe :data="list">
            <el-table-column prop="user.id" label="用户ID"/>
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
    import api from '../../../assets/js/api'
    export default {
        name: "invite-record-list",
        data() {
            return {
                inviteChannelId: '',
                inviteChannelName: '',
                list: [],
                total: 0,
                query: {
                    startTime: '',
                    endTime: '',
                    mobile: '',
                    page: 1,
                },
            }
        },
        computed: {
            title() {
                return `注册人数详情 ( ${this.inviteChannelName}  )`
            }
        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList()
            },
            getList(){
                console.log(this.query)
                this.query.id = this.inviteChannelId
                api.get('inviteChannel/inviteRecords', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            exportExcel(){
                window.open(`/api/oper/inviteChannel/inviteRecords/export?id=${this.query.id}&mobile=${this.query.mobile}&startTime=${this.query.startTime}&endTime=${this.query.endTime}`)
            }
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