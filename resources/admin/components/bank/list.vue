<template>
    <page title="银行库管理" v-loading="isLoading">

            <el-form v-model="query" class="fl" inline size="small">
                <el-form-item prop="name" label="银行名称" >
                    <el-input v-model="query.name" size="small"  placeholder="银行名称"  class="w-200" clearable></el-input>
                </el-form-item>

                <el-form-item label="状态" prop="status">
                    <el-select v-model="query.status" size="small"  multiple placeholder="请选择" class="w-150">
                        <el-option label="启用" value="1"/>
                        <el-option label="禁用" value="2"/>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
            </el-form>


        <el-button class="fr" type="primary" @click="add">添加</el-button>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="id" label="ID"  />
            <el-table-column prop="name" label="银行名称"   />
            <el-table-column prop="status_val" label="状态" >
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">{{scope.row.status_val}}</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">{{scope.row.status_val}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column label="操作" >
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope)">编辑</el-button>
                    <el-button type="text" @click="changeStatus(scope)">{{scope.row.status === 1 ? '禁用' : '启用'}}</el-button>
                    <el-button type="text" @click="del(scope)">删除</el-button>

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

    export default {
        name: "bank-list",
        data() {
            return {

                isLoading: false,
                query: {
                    name: '',
                    status: '',
                    page: 1,
                },
                list: [],
                total: 0,
            }
        },
        methods: {
            search() {
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/bank/list', params).then(data => {
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            add() {
                let param = {};
                this.$prompt(``,'添加银行', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    // type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'text',
                    inputPlaceholder: '输入银行名称，16字以内',
                    inputValidator: (val) => {if(!val || val.length > 16) return '输入银行名称，16字以内'}
                }).then(({value}) => {
                    console.log(value)
                    param = {name:value};
                    api.post('/bank/add', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            del(scope) {
                let param = {};
                this.$confirm('确定删除该银行?').then(() => {
                    param = {id: scope.row.id};
                    api.post('/bank/del', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                })
            },
            changeStatus(scope){
                let opt = scope.row.status === 1 ? '禁用' : '启用';
                this.$confirm(`确认要${opt}吗?`).then( () => {
                    let status = scope.row.status === 1 ? 2 : 1;
                    api.post('/bank/changeStatus', {id: scope.row.id, status: status}).then((data) => {
                        this.getList();
                    }).finally(() => {
                    })
                })
            },
            edit(scope) {
                let param = {};
                this.$prompt(`编辑银行`,'编辑银行', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    // type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'text',
                    inputPlaceholder: '输入银行名称，16字以内',
                    inputValue:scope.row.name,
                    inputValidator: (val) => {if(!val || val.length > 16) return '输入银行名称，16字以内'}
                }).then(({value}) => {
                    console.log(value)
                    param = {id:scope.row.id,name:value};
                    api.post('/bank/edit', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            }
        },
        created(){
            Object.assign(this.query, this.$route.params);
            this.getList();
        }
    }
</script>

<style scoped>

</style>