<template>
    <page title="供应商管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加供应商</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="供应商名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">禁用</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 10)}}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <supplier-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @refresh="getList"/>
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

        <el-dialog title="添加供应商" :visible.sync="isAdd">
            <supplier-form
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import SupplierItemOptions from './supplier-item-options'
    import SupplierForm from './supplier-form'

    export default {
        name: "supplier-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/suppliers', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
            add(){
                this.isAdd = true;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/supplier/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
        },
        created(){
            this.getList();
        },
        components: {
            SupplierItemOptions,
            SupplierForm,
        }
    }
</script>

<style scoped>

</style>