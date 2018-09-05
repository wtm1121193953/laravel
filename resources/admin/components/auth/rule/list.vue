<template>
    <page title="权限管理">

        <el-button class="fr" type="primary" @click="add">添加权限</el-button>
        <el-table :data="rules" stripe>
            <el-table-column type="expand">
                <template slot-scope="scope">
                    <el-form label-width="150px">
                        <el-form-item label="权限节点列表">
                            <div v-for="(item, index) in scope.row.url_all.split(',')" v-if="item" :key="index">
                                <el-tag >{{item}}</el-tag>
                            </div>
                        </el-form-item>
                    </el-form>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="权限名">
                <template slot-scope="scope">
                    <span v-for="n in scope.row.level - 1">|----</span>
                    {{scope.row.name}}
                </template>
            </el-table-column>
            <el-table-column prop="url" label="URL"/>
            <el-table-column prop="sort" label="排序"/>
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
                    <el-button type="text" v-if="scope.row.level==1||scope.row.level==2" @click="addSubRule(scope)">添加子权限</el-button>
                    <el-button type="text" v-if="scope.row.created_at" @click="del(scope)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog title="添加权限" :visible.sync="isAdd">
            <rule-form :pid="addPid" :ppid="addPpid" @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>
        <el-dialog title="编辑权限" :visible.sync="isEdit">
            <rule-form :rule="currentEditRule" :ppid="editPpid" @cancel="isEdit = false" @save="doEdit"/>
        </el-dialog>
    </page>
</template>
<script>
    import api from '../../../../assets/js/api'
    import RuleForm from './form.vue'
    import { mapState, mapActions } from 'vuex'
    export default {
        data(){
            return {
                isAdd: false,
                isEdit: false,
                isLoading: false,
                currentEditRule: null,
                addPid: null,
                addPpid: null,
                editPpid: null,
                addLevel:1,
            }
        },
        computed:{
            ...mapState('auth', [
                'rules'
            ]),
        },
        methods: {
            ...mapActions('auth', [
                'getRules'
            ]),
            add(){
                this.isAdd = true;
                this.addPid = 0;
                this.addPpid = 0;
            },
            doAdd(rule){
                api.post('/rule/add', rule).then(data => {
                    this.isAdd = false;
                    this.getRules();
                })
            },
            addSubRule(scope){
                this.isAdd = true;
                this.addPid = scope.row.id;
                this.addPpid = scope.row.pid;
            },
            edit(scope){
                this.isEdit = true;
                this.editPpid = scope.row.ppid;
                this.currentEditRule = scope.row;
            },
            doEdit(rule){
                api.post('/rule/edit', rule).then(data => {
                    this.isEdit = false;
                    this.getRules();
                })
            },
            del(scope){
                this.$confirm(`确定要删除权限 ${scope.row.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    api.post('/rule/del', {id: scope.row.id}).then(data => {
                        this.getRules();
                    })
                })
            }
        },
        created(){
            this.getRules();
        },
        components: {
            RuleForm
        }
    }
</script>
<style scoped>

</style>
