<template>
    <page title="业务员列表" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="业务员手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="业务员手机号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="id" label="业务员ID" >
                <el-input v-model="query.id" size="small"  placeholder="业务员ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item prop="name" label="业务员昵称" >
                <el-input v-model="query.name" size="small"  placeholder="业务员昵称"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item label="注册时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        size="small"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd 00:00:00"
                ></el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        size="small"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd 23:59:59"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate)}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" size="small" placeholder="请选择" clearable class="w-150">
                    <el-option label="正常" value="1"/>
                    <el-option label="禁用" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item label="身份认证状态" prop="identity_status">
                <el-select v-model="query.identityStatus" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="待审核" value="1"/>
                    <el-option label="审核通过" value="2"/>
                    <el-option label="审核不通过" value="3"/>
                    <el-option label="未提交" value="4"/>
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
            <el-table-column prop="mobile" label="业务员手机号码"/>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="created_at" label="注册时间"/>
            <el-table-column prop="name" label="昵称"/>
            <el-table-column prop="name" label="姓名">
                <template slot-scope="scope">
                    <span>{{scope.row.bizer_identity_audit_record ? scope.row.bizer_identity_audit_record.name : ''}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status == 2" class="c-danger">禁用</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="identity_status" label="认证身份状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.bizer_identity_audit_record">
                        <span v-if="scope.row.bizer_identity_audit_record.status == 1">待审核</span>
                        <span v-else>
                            <span v-if="!scope.row.bizer_identity_audit_record.reason">
                                <span v-if="parseInt(scope.row.bizer_identity_audit_record.status) == 2" class="c-green">审核通过</span>
                                <span v-else-if="parseInt(scope.row.bizer_identity_audit_record.status) == 3" class="c-danger">审核不通过</span>
                                <span v-else class="c-danger">未知状态</span>
                            </span>
                            <el-popover
                                v-else
                                placement="right-start"
                                trigger="hover"
                                :content="scope.row.bizer_identity_audit_record.reason">
                                <span slot="reference">
                                    <span v-if="parseInt(scope.row.bizer_identity_audit_record.status) == 2" class="c-green">审核通过</span>
                                    <span v-else-if="parseInt(scope.row.bizer_identity_audit_record.status) == 3" class="c-danger">审核不通过</span>
                                    <span v-else class="c-danger">未知状态</span>
                                </span>
                            </el-popover>
                        </span>
                    </span>
                    <span v-else class="c-gray">未提交</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <identity-item-options :scope="scope" @refresh="getList"></identity-item-options>
                </template>
            </el-table-column>

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
    import api from '../../../assets/js/api'
    import IdentityItemOptions from './identity-item-options'

    export default {
        name: "member-user-list",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 15,
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
                api.get('/bizer/getList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
                this.getList();
            },
            downloadExcel() {
                let message = '确定要导出当前筛选的业务员列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.query.identityStartDate = this.query.identityStartDate == null ? '' : this.query.identityStartDate;
                this.query.identityEndDate = this.query.identityEndDate == null ? '' : this.query.identityEndDate;
                this.$confirm(message).then(() => {
                    let data = this.query;
                    let params = [];
                    Object.keys(data).forEach((key) => {
                        let value = data[key];
                        if (typeof value === 'undefined' || value == null) {
                            value = '';
                        }
                        if (value instanceof Array) {
                            value.forEach((val) => {
                                params.push([key + '[]', encodeURIComponent(val)].join('='));
                            })
                        } else {
                            params.push([key, encodeURIComponent(value)].join('='));
                        }
                    });
                    let uri = params.join('&');

                    location.href = `/api/admin/bizer/export?${uri}`;
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
            IdentityItemOptions,
        }
    }
</script>

<style scoped>

</style>