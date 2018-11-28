<template>
    <page title="商品详细" :breadcrumbs="{商品列表: '/cs/goods'}">
        <el-row>
            <el-col :span="22">
                <el-form label-width="120px">
                    <el-form-item prop="status_name" label="商品状态">
                        <span v-if="parseInt(goods.status) === 1" class="c-green">上架</span>
                        <span v-else-if="parseInt(goods.status) === 2" class="c-danger">下架</span>
                        <span v-else>未知 ({{goods.status}})</span>
                    </el-form-item>
                    <el-form-item prop="status_name" label="审核状态">
                        <span v-if="parseInt(goods.audit_status) === 1" class="c-warning">审核中</span>
                        <div v-else-if="parseInt(goods.audit_status) === 2"  slot="reference" class="c-green"><span>审核通过</span></div>
                        <div  v-else-if="parseInt(goods.audit_status) === 3" slot="reference" class="c-danger"><span>审核不通过</span><span class="message">{{goods.audit_suggestion}}</span></div>
                        <span v-else>未知 ({{goods.status}})</span>
                    </el-form-item>

                    <el-form-item prop="goods_name" label="商品名称">
                        {{goods.goods_name}}
                    </el-form-item>

                    <el-form-item label="一级分类" prop="cs_platform_cat_id_level1">
                        {{goods.cs_platform_cat_id_level1_name}}
                    </el-form-item>
                    <el-form-item label="二级分类" prop="cs_platform_cat_id_level2">
                        {{goods.cs_platform_cat_id_level2_name}}
                    </el-form-item>

                    <el-form-item prop="market_price" label="市场价">

                        {{goods.market_price}}
                    </el-form-item>
                    <el-form-item prop="price" label="销售价">

                        {{goods.price}}
                    </el-form-item>
                    <el-form-item prop="stock" label="库存">
                        {{goods.stock}}
                    </el-form-item>

                    <el-form-item prop="logo" label="产品logo图">
                        <div v-viewer>
                            <img :src="goods.logo" alt="商家logo" width="200px" height="100px" />
                        </div>
                    </el-form-item>
                    <el-form-item prop="detail_imgs" label="产品详情图">
                        <div class="desc" v-viewer style="display: none;">
                            <img v-for="(item,index) in goods.detail_imgs" :src="item" :key="index" />
                        </div>
                        <el-button v-if="goods.detail_imgs.length > 0" type="text" @click="previewImage('desc')">查看</el-button>
                    </el-form-item>
                    <el-form-item prop="summary" label="商品简介">
                        {{goods.summary}}
                    </el-form-item>
                    <el-form-item prop="certificate1" label="其他证书1">
                        <div class="desc1" v-viewer style="display: none;">
                            <img v-for="(item,index) in goods.certificate1" :src="item" :key="index" />
                        </div>
                        <el-button v-if="goods.certificate1.length > 0" type="text" @click="previewImage('desc1')">查看</el-button>
                    </el-form-item>
                    <el-form-item prop="certificate2" label="其他证书2">
                        <div class="desc2" v-viewer style="display: none;">
                            <img v-for="(item,index) in goods.certificate2" :src="item" :key="index" />
                        </div>
                        <el-button v-if="goods.certificate2.length > 0" type="text" @click="previewImage('desc2')">查看</el-button>
                    </el-form-item>
                    <el-form-item prop="certificate3" label="其他证书3">
                        <div class="desc3" v-viewer style="display: none;">
                            <img v-for="(item,index) in goods.certificate3" :src="item" :key="index" />
                        </div>
                        <el-button v-if="goods.certificate3.length > 0" type="text" @click="previewImage('desc3')">查看</el-button>
                    </el-form-item>
                    <el-form-item prop="audit_suggestion" label="审核意见">
                        <el-input placeholder="最多输入50个汉字"  maxlength="50" v-model="goods.audit_suggestion" :autosize="{minRows: 3}" type="textarea"/>

                    </el-form-item>
                    <el-form-item>
                        <el-button type="success" @click="audit(1)">审核通过</el-button>
                        <el-button type="warning" @click="audit(2)">审核不通过</el-button>
                        <el-button type="primary" @click="back()">返回</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
    </page>
</template>

<script>
    import previewImg from '../../../assets/components/img/preview-img'
    import imgPreviewDialog from '../../../assets/components/img/preview-dialog'
    import api from '../../../assets/js/api'
    import 'viewerjs/dist/viewer.css'

    export default {
        name: "audit",
        props: {

        },
        computed:{

        },
        data(){
            return {
                isShowPreviewImage: false,
                currentPreviewImage: '',
                id: null,
                goods: {
                    goods_name: '',
                    detail_imgs:[],
                    certificate1 :[],
                    certificate2 :[],
                    certificate3:[],
                    audit_suggestion:'',
                },
                auditType:null
            }
        },
        methods: {
            previewImage(viewerEl){
                // this.currentPreviewImage = url;
                // this.isShowPreviewImage = true;

                const viewer = this.$el.querySelector('.' + viewerEl).$viewer
                viewer.show()
            },
            audit(type){
                api.post('/cs_goods/audit', {id: this.id, type: type,audit_suggestion:this.goods.audit_suggestion}).then(data => {
                    this.$message.success(['', '审核通过', '审核不通过', '打回商户池'][type] + ' 成功');
                    this.back()
                })
            },
            getDetail(){
                api.get('goods/detail', {id: this.id,}).then(data => {
                    this.goods = data;
                });
            },
            back(){
                router.push({
                    path: '/cs/goods',
                });
            }
        },
        created(){
            this.id = this.$route.query.id;
            this.auditType = parseInt(this.$route.query.auditType);
            if(!this.id){
                this.$message.error('id不能为空');
                return false;
            }
            this.getDetail();
        },
        components: {
            previewImg,
            imgPreviewDialog,
        }
    }
</script>

<style scoped>

</style>