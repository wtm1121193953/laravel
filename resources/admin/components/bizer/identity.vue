<template>
    <page title="业务员审核列表" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>
            <el-form-item prop="mobile" label="手机号码" >
                <el-input v-model="query.mobile" placeholder="手机号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="id" label="业务员ID" >
                <el-input v-model="query.id" placeholder="业务员ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item prop="identityName" label="姓名" >
                <el-input v-model="query.identityName" placeholder="姓名"  class="w-200" clearable></el-input>
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

            <el-form-item prop="startDate" label="提交认证时间">
                <el-date-picker
                        v-model="query.identityStartDate"
                        type="date"
                        size="small"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd 00:00:00"
                ></el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="query.identityEndDate"
                        type="date"
                        size="small"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd 23:59:59"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.identityStartDate)}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" size="small" placeholder="请选择" class="w-150">
                    <el-option label="正常" value="1"/>
                    <el-option label="禁用" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item label="身份认证状态" prop="identity_status">
                <el-select v-model="query.identityStatus" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="待审核" :value="1"/>
                    <el-option label="审核通过" :value="2"/>
                    <el-option label="审核不通过" :value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
                <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
                <el-dropdown>
                    <el-button type="primary">
                        批量审核<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item @click.native="bizerIdentitySuccess()">审核通过</el-dropdown-item>
                        <el-dropdown-item @click.native="bizerIdentityFail()">审核不通过</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-form-item>

        </el-form>
        <el-table :data="list" stripe @selection-change="handleSelectionChange">
            <el-table-column
                    type="selection"
                    width="55">
            </el-table-column>
            <el-table-column prop="bizer_identity_audit_record.created_at" label="提交认证时间" width="200px"/>
            <el-table-column prop="mobile" label="手机号码" width="120px"/>
            <el-table-column prop="id" label="业务员ID"/>
            <el-table-column prop="created_at" label="注册时间" width="200px"/>
            <el-table-column prop="name" label="姓名">
                <template slot-scope="scope">
                    <span>{{scope.row.bizer_identity_audit_record ? scope.row.bizer_identity_audit_record.name : ''}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="bizer_identity_audit_record.id_card_no" label="身份证号码" width="200"/>
            <el-table-column prop="bizer_identity_audit_record.front_pic" label="身份证正面">
                <template slot-scope="scope">
                    <div
                        class="detail_image"
                        style="height: 50px; width: 50px"
                        v-viewer
                        @click="previewImage($event)"
                        v-if="scope.row.bizer_identity_audit_record"
                    >
                        <img class="img" :src="scope.row.bizer_identity_audit_record.front_pic" width="100%" height="100%" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="bizer_identity_audit_record.opposite_pic" label="身份证反面">
                <template slot-scope="scope">
                    <div
                        class="detail_image"
                        style="height: 50px; width: 50px"
                        v-viewer
                        @click="previewImage($event)"
                        v-if="scope.row.bizer_identity_audit_record"
                    >
                        <img class="img" :src="scope.row.bizer_identity_audit_record.opposite_pic" width="100%" height="100%" />
                    </div>
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
                        <span v-else-if="parseInt(scope.row.bizer_identity_audit_record.status) === 2" class="c-green">
                            <span v-if="scope.row.bizer_identity_audit_record.reason">
                                <el-popover
                                        placement="right-start"
                                        trigger="hover"
                                        :content="scope.row.bizer_identity_audit_record.reason">
                                <span slot="reference">审核通过</span>
                                </el-popover>
                            </span>
                            <span v-else>审核通过</span>
                        </span>
                        <span v-else-if="parseInt(scope.row.bizer_identity_audit_record.status) === 3" class="c-danger">
                            <span v-if="scope.row.bizer_identity_audit_record.reason">
                                <el-popover
                                        placement="right-start"
                                        trigger="hover"
                                        :content="scope.row.bizer_identity_audit_record.reason">
                                <span slot="reference">审核不通过</span>
                                </el-popover>
                            </span>
                            <span v-else>审核不通过</span>
                        </span>
                        <span v-else>未知({{scope.row.bizer_identity_audit_record.status}})</span>
                    </span>
                    <span v-else class="c-gray">未提交</span>
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
                :page-size="query.pageSize"
                :total="total"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import IdentityItemOptions from "./identity-item-options";

    export default {
        name: "member-identity",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 15,
                    mobile: '',
                    id: '',
                    identityName: '',
                    startDate: '',
                    endDate: '',
                    identityStartDate: '',
                    identityEndDate: '',
                    status: '',
                    identityStatus: [1],
                },
                list: [],
                total: 0,
                selection: [],
                currentMerchant: null,
            }
        },
        computed: {
            seletcionIds() {
                let ids = [];
                this.selection.forEach(function (item) {
                    ids.push(item.id);
                });
                return ids;
            }
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
            handleSelectionChange(val) {
                this.selection = val;
            },
            bizerIdentitySuccess() {
                let length = 0;
                let param = {};

                length = this.seletcionIds.length;
                param = {ids: this.seletcionIds, status: 2};

                if (length <= 0) {
                    this.$message.error('请选择审核数据');
                    return;
                }
                this.$confirm(`<div>确定将这${length}条数据审核通过</div>`,'批量审核提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                }).then(() => {
                    api.post('/bizer/identity/audit', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            bizerIdentityFail() {
                let length = 0;
                let param = {};

                length = this.seletcionIds.length;
                param = {ids: this.seletcionIds, status: 3};

                if (length <= 0) {
                    this.$message.error('请选择审核数据');
                    return;
                }
                this.$prompt(`<div>确定将这${length}条数据审核不通过</div>`,'批量审核提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'textarea',
                    inputPlaceholder: '请填写不通过原因，必填，最多50字',
                    inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                }).then(({value}) => {
                    param.reason = value ? value : '';
                    api.post('/bizer/identity/audit', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            previewImage(event){
                event.stopPropagation();
                //预览商品图片
                const viewer = event.currentTarget.$viewer;
                viewer.show();
                return;
            },
            downloadExcel() {
                let message = '确定要导出当前筛选的业务员身份审核列表么？';
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
                }).catch(() => {})
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