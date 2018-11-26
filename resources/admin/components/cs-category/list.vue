<template>
    <page title="超市商品分类" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加商品分类</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID" width="50px"/>
            <el-table-column prop="cat_name" label="商品分类名称">
                <template slot-scope="scope">
                    <span v-for="i in scope.row.level - 1">|---</span>
                    {{scope.row.cat_name}}
                </template>
            </el-table-column>
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
                    <category-item-options
                            :scope="scope"
                            :tree="tree"
                            @change="itemChanged"
                            @refresh="getList"
                            @add-sub="addSub"
                    />
                </template>
            </el-table-column>
        </el-table>


        <el-dialog title="添加商品分类" :visible.sync="isAdd">
            <category-form
                    :parent="parent"
                    :top-list="tree"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import CategoryItemOptions from './category-item-options'
    import CategoryForm from './category-form'

    export default {
        name: "category-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {

                },
                parent: null,
                list: [],
                tree: [],
            }
        },
        computed: {

        },
        methods: {
            addSub(row){
                this.parent = row;
                this.isAdd = true;
            },
            getList(){
                this.isLoading = true;
                api.get('/cs/category/all', this.query).then(data => {
                    this.list = data.list;
                    this.tree = data.tree;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                this.isAdd = true;
                this.parent = null;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/cs/category/add', data).then(() => {
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
            CategoryItemOptions,
            CategoryForm,
        }
    }
</script>

<style scoped>

</style>