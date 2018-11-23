<template>
    <page title="商品管理" v-loading="isLoading">
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="id" label="商品ID" >
                    <el-input v-model="query.id"  placeholder="商品ID" clearable></el-input>
                </el-form-item>
                <el-form-item prop="goods_name" label="商品名称" >
                    <el-input v-model="query.goods_name"  placeholder="商品名称" clearable></el-input>
                </el-form-item>
                <el-form-item prop="merchant_name" label="商户名称" >
                    <el-input v-model="query.merchant_name"  placeholder="商户名称" clearable></el-input>
                </el-form-item>
                <el-form-item label="一级分类" prop="cs_platform_cat_id_level1">
                    <template>
                        <el-select v-model="query.cs_platform_cat_id_level1" placeholder="请选择" @change="getLevel2()">
                            <el-option
                                    v-for="item in cs_platform_cat_id_level1"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </template>

                </el-form-item>

                <el-form-item label="二级分类" prop="cs_platform_cat_id_level2">
                    <template>
                        <el-select v-model="query.cs_platform_cat_id_level2" placeholder="请选择">
                            <el-option
                                    v-for="item in cs_platform_cat_id_level2"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </template>

                </el-form-item>

                <el-form-item label="商品状态" prop="status">
                    <el-select v-model="query.status"  multiple placeholder="请选择" class="w-150">
                        <el-option label="上架" value="1"/>
                        <el-option label="下架" value="2"/>
                    </el-select>
                </el-form-item>

                <el-form-item label="审核状态" prop="auditStatus">
                    <el-select v-model="query.auditStatus"  multiple placeholder="请选择" class="w-150">
                        <el-option label="审核中" value="1"/>
                        <el-option label="审核通过" value="2"/>
                        <el-option label="审核不通过" value="3"/>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary"  @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="success" @click="downloadExcel">导出Excel</el-button>
                </el-form-item>
            </el-form>
        </el-col>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="商品ID"/>
            <el-table-column prop="goods_name" label="商品名称"/>
            <el-table-column prop="cs_merchant.name" label="商户名称">
                <template slot-scope="scope">
                    <div  slot="reference"><p>{{scope.row.cs_merchant.name}}</p><a @click="checkThis(scope.row.cs_merchant_id)" class="c-green">只看他的</a></div>
                </template>
            </el-table-column>
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

    export default {
        name: "cs-goods-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                    goods_name:'',
                    id:'',
                    merchant_name:'',
                    cs_merchant_id:'',
                    status:'',
                    auditStatus:'',
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
            previewImage(event){
                event.stopPropagation()
                //预览商品图片
                const viewer = event.currentTarget.$viewer
                viewer.show()
                return
            },
            checkThis(cs_merchant_id) {

                this.query.cs_merchant_id = cs_merchant_id
                this.getList();
            },
            search() {
                this.query.cs_merchant_id = '';
                this.getList();
            },
            downloadExcel() {
                let message = '确定要导出当前筛选的商品列表么？';

                this.$confirm(message).then(() => {
                    let data = this.query;
                    let params = [];
                    Object.keys(data).forEach((key) => {
                        let value =  data[key];
                        if (typeof value === 'undefined' || value == null) {
                            value = '';
                        }
                        params.push([key, encodeURIComponent(value)].join('='))
                    }) ;
                    let uri = params.join('&');

                    location.href = `/api/oper/cs_goods/download?${uri}`;
                })
            },

        },
        created(){
            this.getLevel1();
            this.getList();
        },
        components: {
            GoodsItemOptions,
        }
    }
</script>

<style scoped>

</style>