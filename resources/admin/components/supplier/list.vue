<template>
    <page title="供应商管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加供应商</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="供应商名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">无效</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope)">编辑</el-button>
                    <el-button type="text" @click="changeStatus(scope)">{{scope.row.status === 1 ? '禁用' : '启用'}}</el-button>
                    <el-button type="text" @click="del(scope)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog title="添加供应商" :visible.sync="isAdd">
            <supplier-form @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>
        <el-dialog title="编辑供应商信息" :visible.sync="isEdit">
            <supplier-form :data="currentEditSupplier" @cancel="isEdit = false" @save="doEdit"/>
        </el-dialog>
    </page>
</template>
<script>
    import api from '../../../assets/js/api'

    import SupplierForm from './supplier-form.vue'
    export default {
        data(){
            return {
                isAdd: false,
                isEdit: false,
                isLoading: false,
                currentEditSupplier: null,
                list: [],
            }
        },
        computed:{

        },
        methods: {
            getList(){
                this.isLoading = true;
                api.get('/suppliers').then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                this.isAdd = true;
            },
            doAdd(rule){
                this.isLoading = true;
                api.post('/supplier/add', rule).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                     this.isLoading = false;
                })
            },
            edit(scope){
                this.isEdit = true;
                this.currentEditSupplier = scope.row;
            },
            doEdit(rule){
                this.isLoading = true;
                api.post('/supplier/edit', rule).then(() => {
                    this.isEdit = false;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            changeStatus(scope){
                let status = scope.row.status === 1 ? 2 : 1;
                this.isLoading = true;
                api.post('/supplier/changeStatus', {id: scope.row.id, status: status}).then(() => {
                    scope.row.status = status;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            del(scope){
                this.$confirm(`确定要删除供应商 ${scope.row.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.isLoading = true;
                    api.post('/supplier/del', {id: scope.row.id}).then(() => {
                        this.getList();
                    }).finally(() => {
                        this.isLoading = false;
                    })
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
            SupplierForm
        }
    }
</script>
<style scoped>

</style>
