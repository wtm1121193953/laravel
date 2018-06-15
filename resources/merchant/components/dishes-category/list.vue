<template>
    <page title="单品分类" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加分类</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="分类名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-green">正常</span>
                    <span v-else-if="parseInt(scope.row.status) === 2" class="c-danger">禁用</span>
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
                    <dishes-category-item-options
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
                :page-size="query.pageSize"
                :total="total"/>

        <el-dialog title="添加分类" :visible.sync="isAdd" width="25%">
            <dishes-category-form
                    ref="form"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import DishesCategoryItemOptions from './dishes-category-item-options'
    import DishesCategoryForm from './dishes-category-form'

    export default {
        name: "dishes-category-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 10,
                },
                list: [],
                total: 0,
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/dishes/categories', this.query).then(data => {
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
                api.post('/dishes/category/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                    this.$refs.form.reset();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
        },
        created(){
            this.getList();
        },
        components: {
            DishesCategoryItemOptions,
            DishesCategoryForm,
        }
    }
</script>

<style scoped>

</style>