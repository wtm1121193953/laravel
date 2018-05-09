<template>
    <!-- 调整商户与商户池表单字段 -->
    <el-row>
        <el-col :span="24">
            <el-form :model="form" label-width="120px" size="small" :rules="formRules" ref="form" @submit.native.prevent>
                <el-col>
                    <div class="title">商户录入信息</div>
                </el-col>
                <!--商户录入信息左侧块-->
                <el-col :span="11">
                    <el-form-item prop="name" label="商户名称">
                        <el-input v-model="form.name"/>
                    </el-form-item>
                    <el-form-item prop="merchant_category" label="所属行业">
                        <el-cascader
                                :options="categoryOptions"
                                :props="{
                                    value: 'id',
                                    label: 'name',
                                    children: 'sub',
                                }"
                                v-model="form.merchant_category">
                        </el-cascader>
                    </el-form-item>
                    <el-form-item prop="business_licence_pic_url" label="营业执照">
                        <image-upload v-model="form.business_licence_pic_url" :limit="1"/>
                    </el-form-item>
                    <el-form-item prop="organization_code" label="营业执照代码">
                        <el-input v-model="form.organization_code"/>
                    </el-form-item>
                </el-col>

                <!-- 商户录入信息右侧块 -->
                <el-col :span="11" :offset="1">
                    <el-form-item prop="lng_and_lat" label="商户位置">
                        {{form.lng_and_lat}}
                        <el-button @click="isShowMap = true">选择位置</el-button>
                        <el-dialog title="更换地理位置" :visible.sync="isShowMap" :modal="false">
                            <amap-choose-point width="100%" height="500px" v-model="form.lng_and_lat" @select="selectMap"/>
                        </el-dialog>
                    </el-form-item>
                    <el-form-item prop="area" label="省/市/区">
                        <el-cascader
                                :options="areaOptions"
                                :props="{
                                    value: 'area_id',
                                    label: 'name',
                                    children: 'sub',
                                }"
                                v-model="form.area">
                        </el-cascader>
                    </el-form-item>
                    <el-form-item prop="address" label="详细地址">
                        <el-input v-model="form.address"/>
                    </el-form-item>
                </el-col>

                <!-- 确定按钮 -->
                <el-col>
                    <el-form-item>
                        <el-button @click="cancel">取消</el-button>
                        <el-button type="primary" @click="save">提交</el-button>
                    </el-form-item>
                </el-col>
            </el-form>
        </el-col>
    </el-row>
</template>
<script>
    import api from '../../../assets/js/api';
    import AmapChoosePoint from '../../../assets/components/amap/amap-choose-point';

    let defaultForm = {
        name: '',
        merchant_category: [],
        business_licence_pic_url: '',
        organization_code: '',

        lng_and_lat: null,
        area: [],
        address: '',
    };
    export default {
        name: 'merchant-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                form: deepCopy(defaultForm),
                categoryOptions: [],
                areaOptions: [],
                isShowMap: false,
                formRules: {
                    name: [
                        {required: true, message: '商家名称不能为空'}
                    ],
                    merchant_category: [
                        {type: 'array', required: true, message: '所属行业不能为空'}
                    ],
                    business_licence_pic_url: [
                        {required: true, message: '营业执照不能为空'},
                    ],
                    organization_code: [
                        {required: true, message: '营业执照代码不能为空'},
                    ],

                    lng_and_lat: [
                        {required: true, message: '商户位置不能为空'},
                    ],
                    area: [
                        {type: 'array', required: true, message: '省/市/区不能为空'},
                    ],
                    address: [
                        {required: true, message: '商户详细地址不能为空'},
                    ],
                },
            }
        },
        methods: {
            initForm(){
                api.get('merchant/categories/tree').then(data => {
                    this.categoryOptions = data.list;
                });
                api.get('area/tree').then(data => {
                    this.areaOptions = data.list;
                });
                if(this.data){
                    let data = this.data;
                    this.form = deepCopy(data);
                    let merchant_category_array = [];
                    if(data.merchant_category_id){
                        data.categoryPath.forEach(function (item) {
                            merchant_category_array.unshift(parseInt(item.id));
                        })
                    }
                    this.form.merchant_category = merchant_category_array;
                    this.form.area = [parseInt(data.province_id), parseInt(data.city_id), parseInt(data.area_id)];
                    this.form.lng_and_lat = [data.lng, data.lat];

                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            resetForm(){
                this.$refs.form.resetFields();
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);

                        if(this.data && this.data.id){
                            data.id = this.data.id;
                        }

                        data.merchant_category_id = (data.merchant_category.length != 0) ? data.merchant_category[data.merchant_category.length - 1] : 0;
                        data.province_id = data.area[0];
                        data.city_id = data.area[1];
                        data.area_id = data.area[2];
                        if(data.lng_and_lat){
                            data.lng = data.lng_and_lat[0];
                            data.lat = data.lng_and_lat[1];
                        }
                        this.$emit('save', data);
                    }
                })
            },
            selectMap(data) {
                this.isShowMap = false;
                this.form.lng_and_lat = data;
            }
        },
        created(){
            this.initForm();
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
            AmapChoosePoint,
        }
    }
</script>
<style scoped>
    .title {
        line-height: 60px;
        text-align: left;
    }
</style>
