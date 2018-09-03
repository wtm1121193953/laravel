<template>
    <page title="用户审核列表" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="用户手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="用户手机号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="id" label="用户ID" >
                <el-input v-model="query.id" size="small"  placeholder="用户ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item prop="name" label="用户姓名" >
                <el-input v-model="query.name" size="small"  placeholder="用户姓名"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="startDate" label="提交认证时间：开始时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="datetime"
                        size="small"
                        placeholder="选择开始日期"
                        value-format="yyyy-MM-dd HH:mm:ss"
                ></el-date-picker>

            </el-form-item>
            <el-form-item prop="startDate" label="结束时间">
                <el-date-picker
                        v-model="query.endDate"
                        type="datetime"
                        size="small"
                        placeholder="选择结束日期"
                        value-format="yyyy-MM-dd HH:mm:ss"
                ></el-date-picker>
            </el-form-item>
            <el-form-item label="认证状态" prop="status">
                <el-select v-model="query.status" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="待审核" value="1"/>
                    <el-option label="审核通过" value="2"/>
                    <el-option label="审核失败" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="提交认证时间"/>
            <el-table-column prop="user.mobile" label="手机号"/>
            <el-table-column prop="user.id" label="用户ID"/>
            <el-table-column prop="user.created_at" label="注册时间"/>
            <el-table-column prop="name" label="姓名"/>
            <el-table-column prop="number" label="身份证号码"/>
            <el-table-column
                    prop="front_pic"
                    label="身份证正面"
                    width="150">
                <template slot-scope="scope">
                    <div v-viewer>
                        <img v-if="scope.row.front_pic" :src="scope.row.front_pic" width="50" height="50" alt="营业执照">
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="opposite_pic"
                    label="身份证反面"
                    width="150">
                <template slot-scope="scope">
                    <div v-viewer>
                        <img v-if="scope.row.opposite_pic" :src="scope.row.opposite_pic" width="50" height="50" alt="营业执照">
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="user.status_val" label="用户状态"/>
            <el-table-column prop="status" label="认证身份状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-warning">待审核</span>
                    <span v-if="parseInt(scope.row.status) === 2" class="c-green">审核通过</span>
                    <span v-if="parseInt(scope.row.status) === 3" class="c-danger">
                         <el-popover
                             placement="right-start"
                             width="150"
                             trigger="hover"
                             :content="scope.row.reason">
                            <el-text slot="reference">审核失败</el-text>
                         </el-popover>
                    </span>
                </template>
            </el-table-column>

            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <identity-item-options
                            :scope="scope"
                            @refresh="getList"
                    />
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
    import MembersItemOptions from './members-item-options'
    import IdentityItemOptions from "./identity-item-options";

    export default {
        name: "member-identity",
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
                api.get('/member/identity', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },

            downloadExcel() {
                let message = '确定要导出当前筛选的用户列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/member/identity_download?'
                        + 'mobile=' + this.query.mobile
                        + '&startDate=' + this.query.startDate
                        + '&endDate=' + this.query.endDate
                        + '&id=' + this.query.id
                        + '&name='+ this.query.name
                        + '&status=' + this.query.status ;
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
            IdentityItemOptions,
            MembersItemOptions,
        }
    }
</script>

<style scoped>
    .message {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 120px;
        font-size: 12px;
        color: gray;
    }
</style>