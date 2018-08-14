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
                <el-button type="success" @click="changeBind">换 绑</el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="success" @click="allChangeBind">全部换绑</el-button>
            </el-form-item>
        </el-form>

        <el-table stripe :data="list" ref="table" @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55"/>
            <el-table-column prop="user.id" label="用户ID"/>
            <el-table-column prop="user.created_at" label="注册时间"/>
            <el-table-column prop="user.mobile" label="手机号"/>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total"
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
                    noPaginate: true,
                },
                multipleSelection: [],
            }
        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList()
            },
            getList(){
                this.query.id = this.inviteChannelId;
                api.get('users/getInviteUsersList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            changeBind() {
                console.log(this.multipleSelection);
                let length = this.multipleSelection.length;
                this.$confirm(`确定将这位${length}用户换绑吗，换绑后不可修改！`, '警告', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    this.$message({
                        type: 'success',
                        message: '删除成功!'
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            allChangeBind() {

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