<template>
    <page title="换绑" :breadcrumbs="{渠道换绑: '/member/changBind'}">
        <el-form inline class="fl" :model="query" size="small">
            <el-form-item prop="mobile" label="手机号">
                <el-input v-model="query.mobile" placeholder="请输入手机号" @keyup.enter.native="search" clearable></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜 索</i></el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="warning" size="small" :disabled="multipleSelection.length <= 0" @click="changeBind(false)">换绑选中用户</el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="warning" @click="changeBind(true)">换绑所有用户</el-button>
            </el-form-item>
        </el-form>

        <el-table stripe :data="list" ref="table" v-loading="tableLoading" @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55"/>
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

        <el-dialog title="换绑" :visible.sync="showChangeBindDialog" width="20%">
            <el-form :model="form" ref="form" :rules="formRules" size="small">
                <el-form-item prop="type" label="换绑渠道类型">
                    <el-radio-group v-model="form.type">
                        <el-radio :label="1">用户</el-radio>
                        <el-radio :label="2" v-if="hasRule('/api/admin/users/changeBindAdmin')">商户</el-radio>
                        <el-radio :label="3" v-if="hasRule('/api/admin/users/changeBindAdmin')">运营中心</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="channelIdOrMobile" :label="hasRule('/api/admin/users/changeBindAdmin') ? '运营和商户的渠道ID或用户手机号码' : '用户手机号码'">
                    <el-input v-model="form.channelIdOrMobile"/>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="commitChangeBind">确定绑定</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "change-bind-list",
        data() {
            let validateChannelIdOrMobile = (rule, value, callback) => {
                if (this.form.type == 1) {
                    if (!(/^1[3456789]\d{9}$/).test(value)) {
                        callback(new Error('手机号码格式不正确'))
                    } else {
                        callback();
                    }
                } else {
                    if (isNaN(value)) {
                        callback(new Error('请填写正确的渠道ID'));
                    } else {
                        callback();
                    }
                }
            };

            return {
                inviteChannelId: '',
                list: [],
                total: 0,
                query: {
                    mobile: '',
                    page: 1,
                },
                tableLoading: false,
                multipleSelection: [],

                showChangeBindDialog: false,
                isAll: false,
                inviteUserRecordIds: [],
                form: {
                    type: 1,
                    channelIdOrMobile: '',
                },
                formRules: {
                    channelIdOrMobile: [
                        {required: true, message: '运营和商户的渠道ID或用户手机号码 不能为空'},
                        {validator: validateChannelIdOrMobile}
                    ]
                }
            }
        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList()
            },
            getList(){
                this.tableLoading = true;
                this.query.id = this.inviteChannelId;
                api.get('users/getInviteUsersList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            resetParam() {
                this.isAll = false;
                this.inviteUserRecordIds = [];
            },
            changeBind(isAll = false) {
                let length = isAll ? this.total : this.multipleSelection.length;
                if (length <= 0) {
                    this.$message.warning('请选择换绑用户');
                    return false;
                }
                let inviteUserRecordIds = [];
                this.multipleSelection.forEach(function (item) {
                    inviteUserRecordIds.push(item.id);
                });
                let message = `确定将该邀请渠道下的全部用户换绑吗，换绑后不可修改！`
                if(!isAll){
                    message = `确定将这${length}位用户换绑吗，换绑后不可修改！`
                }
                this.$confirm(message, '警告', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    this.isAll = isAll;
                    this.inviteUserRecordIds = inviteUserRecordIds;
                    this.showChangeBindDialog = true;
                }).catch(() => {
                    this.resetParam();
                });
            },
            commitChangeBind() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        this.showChangeBindDialog = false;
                        let param = {
                            isAll: this.isAll,
                            type: this.form.type,
                            channelIdOrMobile: this.form.channelIdOrMobile,
                            inviteUserRecordIds: this.inviteUserRecordIds,
                            inviteChannelId: this.inviteChannelId,
                        };
                        const loading = this.$loading({
                            lock: true,
                            text: '换绑中，请稍后...',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.7)'
                        });
                        api.post('users/changeBind', param, false).then(res => {
                            if (res.code === 0) {
                                let data = res.data;
                                loading.close();
                                let message = '换绑完成, 共换绑' + (data.successCount + data.errorCount) + '个用户, 其中换绑成功' + data.successCount + '个, 换绑失败' + data.errorCount + '个。';
                                this.$alert(message);
                                this.getList();
                            } else {
                                this.$message.error(res.message);
                                loading.close();
                            }
                            this.cancel();
                        });
                    }
                });
            },
            cancel() {
                this.showChangeBindDialog = false;
                this.resetParam();
                this.$refs.form.resetFields();
            }
        },
        created(){
            this.inviteChannelId = this.$route.query.id;
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