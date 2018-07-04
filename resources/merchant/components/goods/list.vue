<template>
    <page title="商品管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加商品</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="商品名称"/>
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
            <!--<el-table-column prop="sort" label="排序">-->
                <!--<template slot-scope="scope">-->
                    <!--{{scope.row.sort}}-->
                <!--</template>-->
            <!--</el-table-column>-->
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <goods-item-options
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

        <el-dialog title="添加商品" :visible.sync="isAdd">
            <goods-form
                    ref="addForm"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import GoodsItemOptions from './goods-item-options'
    import GoodsForm from './goods-form'

    export default {
        name: "goods-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 15,
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
                api.get('/goods', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.getList();
            },
            add(){
                router.push({
                    path: '/goods/add',
                });
                this.isAdd = true;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/goods/add', data).then(() => {
                    this.isAdd = false;
                    this.$refs.addForm.resetForm();
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
        },
        created(){
            this.getList();
        },
        components: {
            GoodsItemOptions,
            GoodsForm,
        }
    }
</script>

<style scoped>

</style>