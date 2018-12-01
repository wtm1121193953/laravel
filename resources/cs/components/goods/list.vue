<template>
    <page title="商品管理" v-loading="isLoading">
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="goods_name" label="商品名称" >
                    <el-input v-model="query.goods_name"  placeholder="商品名称"  clearable></el-input>
                </el-form-item>
                <el-form-item label="一级分类" prop="cs_platform_cat_id_level1">
                    <template>
                        <el-select clearable v-model="query.cs_platform_cat_id_level1" placeholder="请选择" @change="getLevel2()">
                            <el-option
                                    v-for="item in cs_platform_cat_id_level1"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value"
                                    :disabled="item.disabled">
                            </el-option>
                        </el-select>
                    </template>

                </el-form-item>

                <el-form-item clearable label="二级分类" prop="cs_platform_cat_id_level2">
                    <template>
                        <el-select v-model="query.cs_platform_cat_id_level2" placeholder="请选择">
                            <el-option
                                    v-for="item in cs_platform_cat_id_level2"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value"
                                    :disabled="item.disabled">
                            </el-option>
                        </el-select>
                    </template>

                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="getList"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
                <el-button class="fr" type="primary" @click="add">添加商品</el-button>

            </el-form>
        </el-col>

        <el-table :data="list" stripe v-loading="tableLoading">

            <el-table-column prop="id" label="商品ID"/>
            <el-table-column prop="goods_name" label="商品名称"/>
            <el-table-column prop="price" label="销售价 ¥"/>
            <el-table-column prop="cs_platform_cat_id_level1_name" label="一级分类"/>
            <el-table-column prop="cs_platform_cat_id_level2_name" label="二级分类"/>
            <el-table-column prop="logo" label="商品图片">
                <template slot-scope="scope">
                    <div class="detail_image" style="height: 50px; width: 50px" v-viewer @click="previewImage($event)">
                        <img class="img" :src="scope.row.logo" width="100%" height="100%" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-green">上架</span>
                    <span v-else-if="parseInt(scope.row.status) === 2" class="c-danger">下架</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.audit_status) === 1" class="c-warning">审核中</span>
                    <div v-else-if="parseInt(scope.row.audit_status) === 2"  slot="reference" class="c-green"><p>审核通过</p></div>
                    <div  v-else-if="parseInt(scope.row.audit_status) === 3" slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span></div>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间">
            </el-table-column>
            <el-table-column prop="sort" label="排序">-->
                <template slot-scope="scope">
                    {{scope.row.sort}}
                </template>
            </el-table-column>

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
                tableLoading: false,
                query: {
                    goods_name:'',
                    cs_platform_cat_id_level1:'',
                    cs_platform_cat_id_level2:'',
                    page: 1,
                    pageSize: 15,
                },
                list: [],
                total: 0,
                cs_platform_cat_id_level1:[],
                cs_platform_cat_id_level2:[],
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
            getLevel1() {
                api.get('/sub_cat', {parent_id:0}).then(data => {

                    this.cs_platform_cat_id_level1 = data;
                })
            },
            getLevel2() {
                if (this.query.cs_platform_cat_id_level1 == 0) {
                    return true;

                }
                api.get('/sub_cat', {parent_id:this.query.cs_platform_cat_id_level1}).then(data => {
                    this.query.cs_platform_cat_id_level2 = ''
                    this.cs_platform_cat_id_level2 = data;
                })
            },
            getList(){
                this.tableLoading = true;
                api.get('/goods', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.tableLoading = false;
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
                    this.$message.success('添加商品成功' )
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            previewImage(event){
                event.stopPropagation()
                //预览商品图片
                const viewer = event.currentTarget.$viewer
                viewer.show()
                return
            },
        },
        created(){
            this.getLevel1();
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