<template>
    <page title="单品管理" v-loading="isLoading">
        <el-col>
            <el-form class="fl" size="small" inline>
                <el-form-item prop="name" label="商品名称">
                    <el-input v-model="query.name" clearable/>
                </el-form-item>
                <el-form-item prop="category_id" label="类别">
                    <el-select v-model="query.category_id" filterable clearable size="small" placeholder="请选择">
                        <el-option
                                v-for="(item, index) in categoryList"
                                :key="index"
                                :label="item.name"
                                :value="item.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
            </el-form>
            <el-button class="fr" type="primary" @click="add">添加商品</el-button>
        </el-col>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="商品名称"/>
            <el-table-column prop="sale_price" label="销售价"/>
            <el-table-column prop="dishes_category.name" label="类别"/>
            <el-table-column prop="detail_image" label="商品图片">
                <template slot-scope="scope">
                    <div class="detail_image" style="height: 50px; width: 50px" v-viewer @click="previewImage($event)">
                        <img class="img" :src="scope.row.detail_image" width="100%" height="100%" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-green">正常</span>
                    <span v-else-if="parseInt(scope.row.status) === 2" class="c-danger">禁用</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>

            <el-table-column prop="is_hot" label="是否热销">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.is_hot) === 0" class="c-danger">否</span>
                    <span v-else-if="parseInt(scope.row.is_hot) === 1" class="c-green">是</span>
                    <span v-else>未知 ({{scope.row.is_hot}})</span>
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
                    <dishes-goods-item-options
                            :scope="scope"
                            @change="itemChanged"
                            :isFirst="isFirstPage && scope.$index == 0"
                            :isLast="isLastPage && scope.$index == list.length - 1"
                            :showSort="isShowSort"
                            :categoryId="query.category_id"
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

        <el-dialog title="添加商品" :visible.sync="isAdd" :close-on-click-modal="false">
            <dishes-goods-form
                    ref="form"
                    :data="{}"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import DishesGoodsItemOptions from './dishes-goods-item-options'
    import DishesGoodsForm from './dishes-goods-form'
    import PreviewImg from '../../../assets/components/img/preview-img'

    //引入预览样式
    import 'viewerjs/dist/viewer.css'

    export default {
        name: "dishesGoods-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 10,
                    name: '',
                    category_id: '',
                },
                list: [],
                total: 0,
                categoryList: [],
                showSort:0,
            }
        },
        computed: {
            isFirstPage(){
                return this.query.page == 1;
            },
            isLastPage(){
                return this.query.page * this.query.pageSize >= this.total;
            },
            isShowSort(){
                return this.showSort!==0;
            }
        },
        methods: {
            getList(){
                console.log(this.query);
                api.get('/dishes/goods', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.showSort=data.showSort;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
            add(){
                this.isAdd = true;
            },
            doAdd(data){
                api.post('/dishes/goods/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                    this.$refs.form.reset();
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            getCategoryList() {
                api.get('/dishes/categories/all').then((data) => {
                    this.categoryList = data.list;
                })
            },
            previewImage(event){
                //预览商品图片
                const viewer = event.currentTarget.$viewer
                viewer.show()
            },
        },
        created(){
            this.getList();
            this.getCategoryList();
        },
        components: {
            DishesGoodsItemOptions,
            DishesGoodsForm,
            PreviewImg
        }
    }
</script>

<style scoped>

</style>