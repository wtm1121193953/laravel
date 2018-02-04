<template>
    <el-container>
        <el-header height="20px">
            <el-breadcrumb>
                <el-breadcrumb-item>权限管理</el-breadcrumb-item>
            </el-breadcrumb>
        </el-header>
        <el-main>
            权限列表
            <el-button class="fr" type="primary" @click="add">添加权限</el-button>
            <el-table :data="rules" stripe>
                <el-table-column type="expand">
                    <template slot-scope="scope">
                        <el-form label-width="150px">
                            <el-form-item label="权限节点列表">
                                <div v-for="(item, index) in scope.row.url_all.split(',')" :key="index">
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
                <el-table-column prop="url" label="URL"></el-table-column>
                <el-table-column prop="sort" label="排序"></el-table-column>
                <el-table-column prop="status" label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 1" class="c-green">正常</span>
                        <span v-else-if="scope.row.status == 2" class="c-danger">无效</span>
                        <span v-else>未知 ({{scope.row.status}})</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope)">编辑</el-button>
                        <el-button type="text" v-if="scope.row.created_at" @click="del">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <el-dialog title="添加权限" :visible.sync="isAdd">
                <rule-form @cancel="isAdd = false" @save="doAdd"></rule-form>
            </el-dialog>
        </el-main>
    </el-container>
</template>
<script>
    import api from '../../../../assets/js/api'
    import RuleForm from './form.vue'
    import { mapState, mapActions } from 'vuex'
    export default {
        data(){
            return {
                isAdd: false
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
            },
            doAdd(rule){
                api.post('/rule/add', rule).then(res => {
                    api.handlerRes(res).then(data => {
                        this.isAdd = false;
                        this.getRules();
                    })
                })
            },
            edit(){

            },
            del(){

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
