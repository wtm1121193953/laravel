<template>
    <!-- 商户录入信息表单 -->
    <el-form :model="form" size="small" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
        <el-col>
            <div class="title">超市商户录入信息</div>
        </el-col>
        <!--商户录入信息表单-->
        <el-col v-if="!readonly" :span="16">
            <el-form-item label="商户类型">
                <span>超市类</span>
            </el-form-item>
            <el-form-item prop="name" label="商户名称">
                <el-input v-model="form.name" placeholder="请填写商户名称"   />
                <div class="tips">须同营业执照名称一致，如营业执照未填写，填法人姓名</div>
            </el-form-item>
            <el-form-item prop="signboard_name" label="招牌名称">
                <el-input v-model="form.signboard_name"/>
            </el-form-item>
            <!--<el-form-item prop="merchant_category" label="所属行业">
                <el-cascader
                        :options="categoryOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="form.merchant_category">
                </el-cascader>
            </el-form-item>-->
            <el-form-item ref="lngAndLat" prop="lng_and_lat" label="商户坐标">
                {{form.lng_and_lat || '请选择位置'}}
            </el-form-item>
            <el-form-item>
                <qmap-choose-point width="100%" height="500px" :shown-markers="[form.lng_and_lat]" @marker-change="selectMap"/>
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

        <!--商户录入信息左侧块 只读 -->
        <el-col v-else :span="16">
            <el-form-item prop="name" label="商户名称">
                {{data.name}}
            </el-form-item>
            <!--<el-form-item prop="merchant_category" label="所属行业">
                <span v-for="item in data.categoryPath" :key="item.id">
                    {{ item.name }}
                </span>
            </el-form-item>-->

            <el-form-item prop="location" label="商户坐标">
                {{[data.lng, data.lat]}}
                <qmap-choose-point width="100%" height="500px" :shown-markers="[[data.lng, data.lat]]" disabled/>
            </el-form-item>
            <el-form-item prop="area" label="省市区">
                {{data.privince}} {{data.city}} {{data.area}}
            </el-form-item>
            <el-form-item prop="address" label="详细地址">
                {{data.address}}
            </el-form-item>
        </el-col>

        <img-preview-dialog :visible.sync="isShowPreviewImage" :url="currentPreviewImage"/>

    </el-form>
</template>
<script>
    import api from '../../../assets/js/api';
    // import AmapChoosePoint from '../../../assets/components/amap/amap-choose-point';
    import imgPreviewDialog from '../../../assets/components/img/preview-dialog'

    import QmapChoosePoint from '../../../assets/components/qmap/qmap-choose-point'

    let defaultForm = {
        /////// 商户录入信息
        name: '',
        signboard_name: '',
        //merchant_category: [],

        // 位置信息
        lng_and_lat: null,
        area: [],
        address: '',
    };
    export default {
        name: 'merchant-pool-info-form',
        props: {
            data: Object,
            readonly: {type: Boolean, default: false}, // 商户录入信息是否只读
        },
        computed:{

        },
        data(){
            return {
                isShowPreviewImage: false,
                currentPreviewImage: '',
                form: deepCopy(defaultForm),
                categoryOptions: [],
                areaOptions: [],
                formRules: {
                    name: [
                        {required: true, message: '商户名称不能为空', trigger: 'change'},
                        {max: 50, message: '商户名称不能超过50个字'}
                    ],
                    signboard_name: [
                        {required: true, message: '招牌名称不能为空'},
                        {max: 20, message: '招牌名称不能超过20个字'}
                    ],
                    /*merchant_category: [
                        {type: 'array', required: true, message: '所属行业不能为空'}
                    ],*/
                    // 位置信息
                    lng_and_lat: [
                        {required: true, message: '商户位置不能为空'},
                    ],
                    area: [
                        {type: 'array', required: true, message: '省/市/区不能为空'},
                    ],
                    address: [
                        {required: true, message: '商户详细地址不能为空', trigger: 'change'},
                        {max: 60, message: '商户详细地址不能超过60个字'}
                    ],
                },
            }
        },
        methods: {
            previewImage(url){
                this.currentPreviewImage = url;
                this.isShowPreviewImage = true;
            },
            initForm(){
                if(this.data){
                    let data = this.data;
                    data.lng = parseFloat(data.lng);
                    data.lat = parseFloat(data.lat);
                    for (let key in defaultForm){
                        this.form[key] = this.data[key];
                    }
                    /*let merchant_category_array = [];
                    if(data.merchant_category_id && data.categoryPathOnlyEnable){
                        data.categoryPathOnlyEnable.forEach(function (item) {
                            merchant_category_array.push(parseInt(item.id));
                        })
                    }
                    this.form.merchant_category = merchant_category_array;*/
                    if (parseInt(data.province_id) == 0 && parseInt(data.city_id) == 0 && parseInt(data.area_id) == 0) {
                        this.form.area = [];
                    }else {
                        this.form.area = [parseInt(data.province_id), parseInt(data.city_id), parseInt(data.area_id)];
                    }

                    if (!data.lng && !data.lat){
                        this.form.lng_and_lat = null;
                    } else {
                        this.form.lng_and_lat = [data.lng, data.lat];
                    }
                }else {
                    this.form = deepCopy(defaultForm);
                }
            },
            cancel(){
                this.$emit('cancel')
            },
            resetForm(){
                this.$refs.form.resetFields();
            },
            getData(){
                let data = deepCopy(this.form);

                if(this.data && this.data.id){
                    data.id = this.data.id;
                }

                //data.merchant_category_id = (data.merchant_category.length != 0) ? data.merchant_category[data.merchant_category.length - 1] : 0;
                data.province_id = data.area[0];
                data.city_id = data.area[1];
                data.area_id = data.area[2];
                if(data.lng_and_lat){
                    data.lng = data.lng_and_lat[0];
                    data.lat = data.lng_and_lat[1];
                }
                return data;
            },
            validate(callback){
                if(this.readonly){
                    callback()
                }else {
                    this.$refs.form.validate((valid) => {
                        if(valid){
                            callback()
                        }
                    })
                }
            },
            selectMap(markers) {
                markers.forEach(marker => {
                    this.form.lng_and_lat = [
                        marker.getPosition().getLng(),
                        marker.getPosition().getLat(),
                    ];
                    this.$refs.lngAndLat.clearValidate();
                })
            }
        },
        created(){
            /*api.get('merchant/categories/tree').then(data => {
                this.categoryOptions = data.list;

                let self = this;
                for (let i = self.categoryOptions.length - 1; i >= 0; i--) {
                    if (!self.categoryOptions[i].sub) {
                        self.categoryOptions.splice(i, 1);
                    }
                }
            });*/
            api.get('area/tree').then(data => {
                this.areaOptions = data.list;
            });
            this.initForm();
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
            QmapChoosePoint,
            imgPreviewDialog,
        }
    }
</script>
<style scoped>
    .title {
        line-height: 60px;
        text-align: left;
    }
</style>
