<template>
    <page title="用户审核列表" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>

            <el-form-item prop="mobile" label="用户手机号码" >
                <el-input v-model="query.mobile" size="small"  placeholder="用户手机号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="id" label="用户ID" >
                <el-input v-model="query.id" size="small"  placeholder="用户ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item prop="name" label="真实姓名" >
                <el-input v-model="query.name" size="small"  placeholder="真实姓名"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="startDate" label="提交认证时间：开始时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        size="small"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() > new Date(query.endDate) - 8.64e7}}"
                ></el-date-picker>

            </el-form-item>
            <el-form-item prop="startDate" label="结束时间">
                <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        size="small"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item label="认证状态" prop="status">
                <el-select v-model="query.status" size="small"  multiple placeholder="请选择" class="w-150">
                    <el-option label="待审核" value="1"/>
                    <el-option label="审核通过" value="2"/>
                    <el-option label="审核不通过" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="id_card_no" label="身份证号码" >
                <el-input v-model="query.id_card_no" size="small"  placeholder="身份证号码"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
                <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
                <el-dropdown>
                    <el-button type="primary">
                        批量审核<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item @click.native="batchIdentitySuccess()">审核通过</el-dropdown-item>
                        <el-dropdown-item @click.native="batchIdentityFail()">审核不通过</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-form-item>

        </el-form>
        <el-table :data="list" stripe @selection-change="handleSelectionChange">
            <el-table-column
                    type="selection"
                    width="55">
            </el-table-column>
            <el-table-column prop="created_at" label="提交认证时间"/>
            <el-table-column prop="user.mobile" label="手机号"/>
            <el-table-column prop="user.id" label="用户ID"/>
            <el-table-column prop="user.created_at" label="注册时间"/>
            <el-table-column prop="name" label="真实姓名"/>
            <el-table-column prop="id_card_no" label="身份证号码" width="250">
                <template slot-scope="scope">
                    <div>
                        ({{scope.row.countryName}})
                        {{scope.row.id_card_no}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="front_pic" label="身份证正面">
                <template slot-scope="scope">
                    <div class="detail_image" style="height: 50px; width: 50px" v-viewer @click="previewImage($event)">
                        <img class="img" :src="scope.row.front_pic" width="100%" height="100%" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="opposite_pic" label="身份证反面">
                <template slot-scope="scope">
                    <div class="detail_image" style="height: 50px; width: 50px" v-viewer @click="previewImage($event)">
                        <img class="img" :src="scope.row.opposite_pic" width="100%" height="100%" />
                    </div>
                </template>
            </el-table-column>

            <el-table-column prop="user.status_val" label="用户状态"/>
            <el-table-column prop="status" label="认证身份状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-warning">待审核</span>
                    <span v-if="parseInt(scope.row.status) === 2" class="c-green">
                        <span v-if="scope.row.reason">
                            <el-popover
                                    placement="right-start"
                                    trigger="hover"
                                    :content="scope.row.reason">
                            <span slot="reference">审核通过</span>
                            </el-popover>
                        </span>
                        <span v-else>审核通过</span>
                    </span>
                    <span v-if="parseInt(scope.row.status) === 3" class="c-danger">
                        <span v-if="scope.row.reason">
                            <el-popover
                                    placement="right-start"
                                    trigger="hover"
                                    :content="scope.row.reason">
                            <span slot="reference">审核不通过</span>
                            </el-popover>
                        </span>
                        <span v-else>审核不通过</span>
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
                    is_card_no:''
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
                api.get('/member/identity', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            handleSelectionChange(val) {
                this.selection = val;
            },
            batchIdentitySuccess() {
                let length = 0;
                let param = {};

                length = this.seletcionIds.length;
                param = {ids: this.seletcionIds,type:1};

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
                    api.post('/member/batch_identity', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            batchIdentityFail() {
                let length = 0;
                let param = {};

                length = this.seletcionIds.length;
                param = {ids: this.seletcionIds,type:2};

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
                    inputType: 'text',
                    inputPlaceholder: '请填写不通过原因，必填，最多50字',
                    inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                }).then(({value}) => {
                    param.reason = value ? value : '';
                    api.post('/member/batch_identity', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            previewImage(event){
                event.stopPropagation()
                //预览商品图片
                const viewer = event.currentTarget.$viewer
                viewer.show()
                return
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

</style>