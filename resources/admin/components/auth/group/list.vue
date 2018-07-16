<template>
    <page title="角色管理">
        <el-button class="fr" type="primary" @click="add">添加角色</el-button>
        <el-table :data="groups" stripe>
            <el-table-column type="expand">
                <template slot-scope="scope">
                    <el-form label-width="150px">
                        <el-form-item label="拥有的权限列表">
                            <div v-for="(item, index) in scope.row.rule_ids.split(',')" v-if="item" :key="index">
                                <el-tag >{{item}}</el-tag>
                            </div>
                        </el-form-item>
                    </el-form>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="角色名"/>
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
                    <el-button type="text" @click="edit(scope)">修改</el-button>
                    <el-button type="text" @click="changeStatus(scope)">{{scope.row.status == 1 ? '禁用' : '启用'}}</el-button>
                    <el-button type="text" @click="del(scope)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog :visible.sync="isAdd" title="添加角色">
            <group-form @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>

        <el-dialog :visible.sync="isEdit" title="修改角色信息">
            <group-form :group="currentEditGroup" @cancel="isEdit = false" @save="doEdit"/>
        </el-dialog>
    </page>
</template>

<script>
    import GroupForm from './form'
    import {mapState, mapActions} from 'vuex';

    export default {
        name: "group-list",
        data(){
            return {
                isAdd: false,
                isEdit: false,
                isLoading: false,
                currentEditGroup: null,
            }
        },
        computed: {
            ...mapState('auth', [
                'groups'
            ])
        },
        methods: {
            ...mapActions('auth', [
                'getGroups',
                'getRuleTree'
            ]),
            add(){
                this.isAdd = true;
            },
            doAdd(group){
                this.isLoading = true;
                api.post('/group/add', group).then(() => {
                    this.isAdd = false;
                    this.getGroups();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            edit(scope){
                this.isEdit = true;
                this.currentEditGroup = scope.row;
            },
            doEdit(group){
                this.isLoading = true;
                api.post('/group/edit', group).then(() => {
                    this.isEdit = false;
                    this.getGroups();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            changeStatus(scope){
                let status = scope.row.status === 1 ? 2 : 1;
                this.isLoading = true;
                api.post('/group/changeStatus', {id: scope.row.id, status: status}).then(() => {
                    scope.row.status = status;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            del(scope){
                this.$confirm(`确定要删除角色 ${scope.row.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.isLoading = true;
                    api.post('/group/del', {id: scope.row.id}).then(() => {
                        this.getGroups();
                    }).finally(() => {
                        this.isLoading = false;
                    })
                })
            }
        },
        created(){
            this.getGroups();
            this.getRuleTree();
        },
        components: {
            GroupForm
        }
    }
</script>

<style scoped>

</style>