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
                    <preview-img :url="scope.row.default_image" width="40px" height="40px" alt=""/>
                </template>
            </el-table-column>
            <el-table-column label="库存">
                <template slot-scope="scope">
                    <span>{{scope.row.stock}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="origin_price" label="商品价格"/>
            <el-table-column prop="discount_price" label="商品折扣价"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">已上架</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已下架</span>
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
                    <goods-item-options :scope="scope" @change="itemChanged" @refresh="getList"/>
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

        <el-dialog title="添加商品" width="80%" :visible.sync="isAdd">
            <goods-form
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>
<script>
    import api from '../../../assets/js/api'

    import GoodsForm from './goods-form.vue'
    import PreviewImg from "../../../assets/components/img/preview-img";
    import GoodsItemOptions from './goods-item-options'
    import {mapState, mapGetters} from 'vuex'

    export default {
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,

                // 库存管理
                showManageStockDialog: false,
            }
        },
        computed:{
            ...mapState('goods', [
                'suppliers',
                'categories',
            ]),
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
            getList(){
                this.isLoading = true;
                api.get('/goods', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                this.isAdd = true;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/goods/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                     this.isLoading = false;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            }
        },
        created(){
            this.getList();
            store.dispatch('goods/getAllSuppliers')
            store.dispatch('goods/getAllCategories')
        },
        watch: {
        },
        components: {
            PreviewImg,
            GoodsForm,
            GoodsItemOptions,
        }
    }
</script>
<style scoped>

</style>
