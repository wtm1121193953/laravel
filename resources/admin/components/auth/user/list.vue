<template>
    <page title="用户管理">
        <el-button class="fr" type="primary" @click="add">添加用户</el-button>
        <el-table :data="users" stripe>
            <el-table-column prop="id" label="用户ID"/>
            <el-table-column prop="username" label="用户名"/>
            <el-table-column label="所属分组">
                <template slot-scope="scope">
                    <span v-if="scope.row.super == 1">超级管理员</span>
                    <span v-else>
                            {{groupsIdMapping[scope.row.group_id] ? groupsIdMapping[scope.row.group_id].name : ''}}
                        </span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">无效</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="创建时间"/>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope)">编辑</el-button>
                    <el-button type="text" v-if="user.super === 1" @click="resetPassword(scope)">重置密码</el-button>
                    <el-button type="text" v-if="scope.row.super !== 1" @click="del(scope)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog title="添加用户" :visible.sync="isAdd">
            <user-form @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>
        <el-dialog title="修改用户信息" :visible.sync="isEdit" :before-close="handleClose">
            <user-form :user="currentEditUser" @cancel="isEdit = false"  ref="userForm" @save="doEdit"   />
        </el-dialog>
    </page>
</template>
<script>
    import api from '../../../../assets/js/api'
    import UserForm from './form.vue'
    import { mapState, mapActions } from 'vuex'
    export default {
        name: 'user-list',
        data(){
            return {
                isAdd: false,
                isEdit: false,
                isLoading: false,
                currentEditUser: null,
            }
        },
        computed:{
            ...mapState([
                'user'
            ]),
            ...mapState('auth', [
                'users',
                'groupsIdMapping'
            ]),
        },
        methods: {
            ...mapActions('auth', [
                'getUsers',
                'getGroups',
            ]),
            add(){
                this.isAdd = true;
            },
            doAdd(rule){
                api.post('/user/add', rule).then(data => {
                    this.isAdd = false;
                    this.getUsers();
                })
            },
            edit(scope){
                this.isEdit = true;
                this.currentEditUser = scope.row;
            },
            doEdit(rule){
                api.post('/user/edit', rule).then(data => {
                    this.isEdit = false;
                    this.getUsers();
                })
            },
            resetPassword(scope){
                this.$prompt(`请输入密码`, `重置用户 ${scope.row.username} 的密码`, {}).then(({value}) => {
                    api.post('/user/resetPassword', {id: scope.row.id, password: value}).then(data => {
                        this.$message.success('重置密码成功')
                    })
                })
            },
            del(scope){
                this.$confirm(`确定要删除用户 ${scope.row.username} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    api.post('/user/del', {id: scope.row.id}).then(data => {
                        this.getUsers();
                    })
                })
            },
            handleClose(){
                this.$refs.userForm.cancel()
            }
        },
        created(){
            this.getUsers();
            this.getGroups();
        },
        components: {
            UserForm
        }
    }
</script>
<style scoped>

</style>
