<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="goods_name" label="商品名称">
                    <el-input v-model="form.goods_name"/>
                </el-form-item>
                <el-form-item label="一级分类" prop="cs_platform_cat_id_level1">
                    <template>
                        <el-select v-model="form.cs_platform_cat_id_level1" placeholder="请选择" @change="changeLevel2()">
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

                <el-form-item label="二级分类" prop="cs_platform_cat_id_level2">
                    <template>
                        <el-select v-model="form.cs_platform_cat_id_level2" placeholder="请选择">
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
                <el-form-item prop="market_price" label="市场价">
                    <el-input-number v-model="form.market_price" :min="0"/>
                </el-form-item>
                <el-form-item prop="price" label="销售价">
                    <el-input-number v-model="form.price" :min="0"/>
                </el-form-item>
                <el-form-item prop="stock" label="库存">
                    <el-input-number v-model="form.stock" :min="0" :max="100000"/>
                </el-form-item>

                <el-form-item prop="logo" label="产品主图">
                    <image-upload :width="750" :height="750" v-model="form.logo" :limit="1"/>
                    <div>图片尺寸: 750 px * 750 px</div>
                </el-form-item>
                <el-form-item prop="pic_list" label="产品详情图">
                    <image-upload :width="750" :height="750" v-model="form.detail_imgs" :limit="6"/>
                    <div>图片尺寸: 750 px * 750 px</div>
                </el-form-item>
                <el-form-item prop="summary" label="商品简介">
                    <el-input v-model="form.summary" :autosize="{minRows: 2}" type="textarea"/>
                </el-form-item>
                <el-form-item prop="pic_list" label="其他证书1">
                    <image-upload  v-model="form.certificate1" :limit="6"/>
                    <div></div>
                </el-form-item>
                <el-form-item prop="pic_list" label="其他证书2">
                    <image-upload  v-model="form.certificate2" :limit="6"/>
                    <div></div>
                </el-form-item>
                <el-form-item prop="pic_list" label="其他证书3">
                    <image-upload v-model="form.certificate3" :limit="6"/>
                    <div></div>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>

</template>
<script>
    import api from '../../../assets/js/api'
    let defaultForm = {
        goods_name: '',
        cs_platform_cat_id_level1: '',
        cs_platform_cat_id_level2: '',
        market_price: 0,
        price: 0,
        stock: 0,
        detail_imgs: [],
        logo: '',
        summary: '',
        certificate1:[],
        certificate2:[],
        certificate3:[],
        categoryOptions:[]
    };
    export default {
        name: 'goods-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            var validatePrice = (rule, value, callback) => {
                if (value <= 0 || value>=1000000){
                    callback(new Error('销售价必须在0到1000000元之间'));
                }else {
                    callback();
                }
            };
            var validateMarketPrice = (rule, value, callback) => {
                if (value <= 0 || value>=1000000) {
                    callback(new Error('市场价必须在0到1000000元之间'));
                }else {
                    callback();
                }
            };
            return {
                form: deepCopy(defaultForm),
                cs_platform_cat_id_level1:[],
                cs_platform_cat_id_level2:[],
                formRules: {
                    goods_name: [
                        {required: true, message: '商品名称不能为空'},
                        {max: 30, message: '商品名称不能超过30个字'}
                    ],
                    cs_platform_cat_id_level1: [
                        {required: true, message: '一级分类不能为空'},
                    ],
                    cs_platform_cat_id_level2: [
                        {required: true, message: '二级分类不能为空'},
                    ],
                    market_price: [
                        {required: true, message: '市场价不能为空'},
                        {validator: validateMarketPrice, trigger: 'blur'}
                    ],
                    price: [
                        {required: true, message: '销售价不能为空'},
                        {validator: validatePrice, trigger: 'blur'}
                    ],
                    stock: [
                        {type: 'number', max: 100000, message: '库存不能超过100000'}
                    ],
                    summary: [
                        {required: true, message: '简介不能为空'},
                        {max: 200, message: '商品简介不能超过200个字'}
                    ],
                    logo: [
                        {required: true, message: '缩略图不能为空'}
                    ],
                    detail_imgs: [
                        {required: true, message: '详情图不能为空'}
                    ],
                },
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data);
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            getLevel1() {
                api.get('/sub_cat', {parent_id:0}).then(data => {


                    this.cs_platform_cat_id_level1 = data;
                })
            },
            changeLevel2() {

                this.form.cs_platform_cat_id_level2 = '';
                this.getLevel2();
            },
            getLevel2() {
                if (this.form.cs_platform_cat_id_level1 == 0) {
                    return true;

                }
                api.get('/sub_cat', {parent_id:this.form.cs_platform_cat_id_level1}).then(data => {
                    this.cs_platform_cat_id_level2 = data;
                })
            },
            resetForm(){
                this.$refs.form.resetFields();
                console.log(this.form)
            },
            cancel(){
                console.log(this.form)
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        if(this.data && this.data.id){
                            data.id = this.data.id;
                        }
                        this.$emit('save', data);
                    }
                })

            }
        },
        created(){
            this.initForm();
            this.getLevel1();
            if (this.form.cs_platform_cat_id_level2 !=0) {
                this.getLevel2();
            }
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
        }
    }
</script>
<style scoped>

</style>
