<template>
    <page title="分类管理" v-loading="isLoading">
        <el-table :data="list" stripe v-loading="tableLoading">
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="cs_cat_name" label="分类名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-green">上架</span>
                    <span v-else-if="parseInt(scope.row.status) === 2" class="c-danger">下架</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                    <span v-if="parseInt(scope.row.platform_cat_status) === 2">(平台已禁用)</span>

                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间">

            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <cs-category-item-options
                            :scope="scope"
                            :isFirst="isFirstPage && scope.$index == 0"
                            :isLast="isLastPage && scope.$index == list.length - 1"
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

    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import CsCategoryItemOptions from './cs-category-item-options'
    import CsCategoryForm from './cs-category-form'

    export default {
        name: "cs-category-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                tableLoading: false,
                query: {
                    page: 1,
                    pageSize: 10,
                },
                list: [],
                total: 0,
            }
        },
        computed: {
            isFirstPage(){
                return this.query.page == 1;
            },
            isLastPage(){
                return this.query.page * this.query.pageSize >= this.total;
            }
        },
        methods: {
            getList(){
                this.tableLoading = true;
                api.get('/categories', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.tableLoading = false;
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
            resetForm() {
                this.$refs.form.reset();
            }
        },
        created(){
            this.getList();
        },
        components: {
            CsCategoryItemOptions,
            CsCategoryForm,
        }
    }
</script>

<style scoped>

</style>