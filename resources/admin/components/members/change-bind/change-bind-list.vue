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
        name: "change-bind-list",
        data() {
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
                    this.commitChangeBind(isAll, inviteUserRecordIds);
                }).catch(() => {

                });
            },
            commitChangeBind(isAll, inviteUserRecordIds = []) {
                this.$prompt('绑定新帐号', '警告', {
                    confirmButtonText: '确定绑定',
                    cancelButtonText: '取消',
                    inputPattern: /^1[3456789]\d{9}$/,
                    inputErrorMessage: '手机号码格式不正确',
                    inputPlaceholder: '输入换绑新用户的手机号码',
                }).then(({ value }) => {
                    let param = {
                        isAll: isAll,
                        mobile: value,
                        inviteUserRecordIds: inviteUserRecordIds,
                        inviteChannelId: this.inviteChannelId,
                    };
                    api.post('users/changeBind', param).then(data => {
                        let message = '换绑完成, 共换绑' + (data.successCount + data.errorCount) + '个用户, 其中换绑成功' + data.successCount + '个, 换绑失败' + data.errorCount + '个。';
                        this.$alert(message);
                        this.getList();
                    })
                }).catch(() => {

                });
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