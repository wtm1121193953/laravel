<template>
    <page title="商品管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加商品</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="商品名称"/>
            <el-table-column label="分类">
                <template slot-scope="scope">
                    {{getCategoryNameById(scope.row.category_id)}}
                </template>
            </el-table-column>
            <el-table-column label="所属供应商">
                <template slot-scope="scope">
                    {{getSupplierNameById(scope.row.supplier_id)}}
                </template>
            </el-table-column>
            <el-table-column label="商品图片">
                <template slot-scope="scope">
                    <preview-img :url="scope.row.pict_url" width="40px" height="40px" alt=""/>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">已上架</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已下架</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope)">编辑</el-button>
                    <el-button type="text" @click="changeStatus(scope)">{{scope.row.status === 1 ? '下架' : '上架'}}</el-button>
                    <el-button type="text" @click="del(scope)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog title="添加商品" :visible.sync="isAdd">
            <item-form
                    :suppliers="suppliers"
                    :categories="categories"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
        <el-dialog title="编辑商品信息" :visible.sync="isEdit">
            <item-form
                    :suppliers="suppliers"
                    :categories="categories"
                    :data="currentEditItem"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </page>
</template>
<script>
    import api from '../../../assets/js/api'

    import ItemForm from './item-form.vue'
    import PreviewImg from "../../../assets/components/preview-img";
    export default {
        data(){
            return {
                isAdd: false,
                isEdit: false,
                isLoading: false,
                currentEditItem: null,
                list: [],
                suppliers: [],
                categories: [],
            }
        },
        computed:{

        },
        methods: {
            getSupplierNameById(supplierId){
                let name = '未知(已删除)';
                this.suppliers.forEach(item => {
                    if(item.id === supplierId){
                        name = item.name;
                    }
                })
                return name;
            },
            getCategoryNameById(categoryId){
                let name = '未知(已删除)';
                this.categories.forEach(item => {
                    if(item.id === categoryId){
                        name = item.name;
                    }
                })
                return name;
            },
            getEnableSupplierList(){
                api.get('/suppliers/all', {status: 1}).then(data => {
                    this.suppliers = data.list;
                })
            },
            getCategories(){
                api.get('/categories/all', {status: 1}).then(data => {
                    this.categories = data.list;
                })
            },
            getList(){
                this.isLoading = true;
                api.get('/items').then(data => {
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
                api.post('/item/add', rule).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                     this.isLoading = false;
                })
            },
            edit(scope){
                this.isEdit = true;
                this.currentEditItem = scope.row;
            },
            doEdit(rule){
                this.isLoading = true;
                api.post('/item/edit', rule).then(() => {
                    this.isEdit = false;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            changeStatus(scope){
                let status = scope.row.status === 1 ? 2 : 1;
                this.isLoading = true;
                api.post('/item/changeStatus', {id: scope.row.id, status: status}).then(() => {
                    scope.row.status = status;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            del(scope){
                this.$confirm(`确定要删除商品 ${scope.row.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.isLoading = true;
                    api.post('/item/del', {id: scope.row.id}).then(() => {
                        this.getList();
                    }).finally(() => {
                        this.isLoading = false;
                    })
                })
            }
        },
        created(){
            this.getList();
            this.getEnableSupplierList();
            this.getCategories();
        },
        watch: {
        },
        components: {
            PreviewImg,
            ItemForm,
        }
    }
</script>
<style scoped>

</style>
