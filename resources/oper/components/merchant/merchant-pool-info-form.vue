<template>
    <!-- 商户录入信息表单 -->
    <el-form :model="form" size="small" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
        <el-col>
            <div class="title">商户录入信息</div>
        </el-col>
        <!--商户录入信息左侧块-->
        <el-col v-if="!readonly" :span="11">
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

        <!--商户录入信息左侧块 只读 -->
        <el-col v-else :span="11">
            <el-form-item prop="name" label="商户名称">
                {{data.name}}
            </el-form-item>
            <el-form-item prop="merchant_category" label="所属行业">
                <span v-for="item in data.categoryPath" :key="item.id">
                    {{ data.name }}
                </span>
            </el-form-item>
            <el-form-item label="营业执照">
                <el-button type="text" @click="previewImage(data.business_licence_pic_url)">查看</el-button>
            </el-form-item>
            <el-form-item label="营业执照代码">
                {{ data.organization_code}}
            </el-form-item>
        </el-col>

        <!-- 商户录入信息右侧块 -->
        <el-col v-if="!readonly" :span="11" :offset="1">
            <el-form-item prop="lng_and_lat" label="商户位置">
                {{form.lng_and_lat || '请选择位置'}}
            </el-form-item>
            <el-form-item>
                <qmap-choose-point width="100%" height="500px" @marker-change="selectMap"/>
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

        <!-- 商户录入信息右侧块 只读 -->
        <el-col v-else :span="11" :offset="1">
            <el-form-item prop="location" label="商户位置">
                {{data.lng}} , {{data.lat}}
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
        merchant_category: [],
        business_licence_pic_url: '',
        organization_code: '',
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
                    // 位置信息
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
            previewImage(url){
                this.currentPreviewImage = url;
                this.isShowPreviewImage = true;
            },
            initForm(){
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

                data.merchant_category_id = (data.merchant_category.length != 0) ? data.merchant_category[data.merchant_category.length - 1] : 0;
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
                })
            }
        },
        created(){
            api.get('merchant/categories/tree').then(data => {
                this.categoryOptions = data.list;
            });
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
