<template>
    <page title="用户列表" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="用户手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="用户手机号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="id" label="用户ID" >
                <el-input v-model="query.id" size="small"  placeholder="用户ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item prop="name" label="用户名称" >
                <el-input v-model="query.name" size="small"  placeholder="会员名称"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="startDate" label="注册时间：开始时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        size="small"
                        placeholder="选择开始日期"
                        value-format="yyyy-MM-dd"
                ></el-date-picker>

            </el-form-item>
            <el-form-item prop="startDate" label="结束时间">
                <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        size="small"
                        placeholder="选择结束日期"
                        value-format="yyyy-MM-dd"
                ></el-date-picker>
            </el-form-item>
            <!--
            <el-form-item label="用户状态" prop="status">
                <el-select v-model="query.status" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="正常" value="1"/>
                    <el-option label="禁用" value="2"/>
                </el-select>
            </el-form-item>

            <el-form-item label="用户认证状态" prop="identity_status">
                <el-select v-model="query.identityStatus" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="待审核" value="1"/>
                    <el-option label="审核通过" value="2"/>
                    <el-option label="审核不通过" value="3"/>
                </el-select>
            </el-form-item>
            -->

            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column prop="mobile" label="手机号"/>
            <<el-table-column prop="id" label="用户id"/>
            <el-table-column prop="created_at" label="注册时间"/>
            <el-table-column prop="name" label="用户名称"/>
            <el-table-column prop="parent" label="分享人"/>
            <el-table-column prop="stauts_val" label="用户状态"/>
            <el-table-column prop="identity_status_text" label="认证身份状态"/>

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
    import MembersItemOptions from './members-item-options'

    export default {
        name: "member-user-list",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                    mobile: '',
                    id: '',
                    name: '',
                    startDate: '',
                    endDate: '',
                    status: '',
                    identityStatus: '',
                },
                list: [],
                total: 0,
                currentMerchant: null,
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
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/member/download?'
                        + 'mobile=' + this.query.mobile
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&id=' + this.query.id
                        + '&name='+ this.query.name
                        + '&status=' + this.query.status
                        + '&identityStatus=' + this.query.identityStatus ;
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
            MembersItemOptions,
        }
    }
</script>

<style scoped>

</style>