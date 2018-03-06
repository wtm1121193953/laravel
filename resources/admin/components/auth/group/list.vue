<template>
    <el-container>
        <el-header height="20px">
            <el-breadcrumb>
                <el-breadcrumb-item>角色管理</el-breadcrumb-item>
            </el-breadcrumb>
        </el-header>
        <el-main>
            角色列表
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
        </el-main>
    </el-container>
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
                api.post('/group/add', group).then(data => {
                    this.isAdd = false;
                    this.getGroups();
                })
            },
            edit(scope){
                this.isEdit = true;
                this.currentEditGroup = scope.row;
            },
            doEdit(group){
                api.post('/group/edit', group).then(data => {
                    this.isEdit = false;
                    this.getGroups();
                })
            },
            del(scope){
                this.$confirm(`确定要删除角色 ${scope.row.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    api.post('/group/del', {id: scope.row.id}).then(data => {
                        this.getGroups();
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