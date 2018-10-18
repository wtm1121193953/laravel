<template>
    <el-form :model="form" :rules="formRules" ref="form" size="small" label-width="120px">
        <el-col>
            <div class="title">商户录入信息</div>
        </el-col>
        <!--商户录入信息表单-->
        <el-col :span="16">
            <el-form-item prop="name" label="商户名称" class="w-500">
                <el-input v-model="form.name" placeholder="请填写商户名称"   />
                <div class="tips">须同营业执照名称一致，如营业执照未填写，填法人姓名</div>
            </el-form-item>
            <el-form-item prop="signboard_name" label="招牌名称" class="w-500">
                <el-input v-model="form.signboard_name"/>
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
            <el-form-item prop="contacter" label="负责人姓名" class="w-500">
                <el-input v-model="form.contacter"/>
            </el-form-item>
            <el-form-item prop="contacter_phone" label="负责人手机号码" class="w-500">
                <el-input v-model="form.contacter_phone"/>
            </el-form-item>
            <el-form-item prop="bizer_id" label="签约人">
                <el-select
                        v-model="form.bizer_id"
                        filterable
                        :filter-method="filterOperBizers"
                        clearable
                        placeholder="请输入业务员昵称或手机号码"
                        class="w-300"
                >
                    <el-option
                            v-for="item in operBizers"
                            :key="item.bizerId"
                            :label="item.bizerName"
                            :value="item.bizerId">
                        <span class="c-blue">{{item.bizerName}}</span>
                        <span class="c-light-gray">{{item.bizerMobile}}</span>
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item prop="service_phone" label="客服电话" class="w-500">
                <el-input v-model="form.service_phone"/>
            </el-form-item>
            <el-form-item prop="logo" label="商家logo">
                <image-upload :width="190" :height="190" v-model="form.logo" :limit="1"/>
                <div>图片尺寸: 190 px * 190 px</div>
            </el-form-item>
            <el-form-item prop="desc_pic_list" label="商家介绍图片">
                <image-upload :width="752" :height="398" v-model="form.desc_pic_list" :limit="6"/>
                <div>图片尺寸: 752 px * 398 px</div>
            </el-form-item>

            <el-form-item prop="business_licence_pic_url" label="营业执照">
                <image-upload  v-model="form.business_licence_pic_url" :limit="1"/>
            </el-form-item>
            <el-form-item prop="organization_code" label="营业执照代码">
                <el-input v-model="form.organization_code"/>
            </el-form-item>
        </el-col>

        <el-col>
            <el-form-item>
                <el-button @click="cancel">取消</el-button>
                <el-button type="success" @click="save">保存并提审</el-button>
            </el-form-item>
        </el-col>
    </el-form>
</template>
<script>
    import api from '../../../assets/js/api';
    import QmapChoosePoint from '../../../assets/components/qmap/qmap-choose-point'

    let defaultForm = {
        name: '',
        signboard_name: '',
        merchant_category: [],
        // 位置信息
        lng_and_lat: null,
        area: [],
        address: '',

        contacter: '',
        contacter_phone: '',
        oper_biz_member_code: '',
        service_phone: '',
        logo: '',
        desc_pic_list: [],
        business_licence_pic_url:'',
        organization_code:'',

        is_pilot: 1,
    };
    let validateContacterPhone = (rule, value, callback) => {
        if (!(/^1[3,4,5,6,7,8,9]\d{9}$/.test(value))) {
            callback(new Error('请输入正确的手机号码'));
        }else {
            callback();
        }
    };

    export default {
        name: 'pilot-merchant-form',
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
                operBizers: [],
                formRules: {
                    name: [
                        {required: true, message: '商户名称不能为空', trigger: 'change'},
                        {max: 20, message: '商户名称不能超过20个字'}
                    ],
                    signboard_name: [
                        {required: true, message: '招牌名称不能为空'},
                        {max: 20, message: '招牌名称不能超过20个字'}
                    ],
                    merchant_category: [
                        {type: 'array', required: true, message: '所属行业不能为空'}
                    ],
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
                    contacter: [
                        {required: true, message: '商户负责人姓名 不能为空'},
                    ],
                    contacter_phone: [
                        {required: true, message: '负责人手机号码 不能为空'},
                        {validator: validateContacterPhone},
                    ],
                    service_phone: [
                        {required: true, message: '客服电话 不能为空'},
                        {max: 15, message: '客服电话不能超过15个字'}
                    ],
                    logo: [
                        {required: true, message: '商家logo不能为空', trigger: 'change'}
                    ],
                    desc_pic_list: [
                        {required: true, message: '商家介绍图片不能为空'}
                    ],
                    business_licence_pic_url: [
                        {required: true, message: '营业执照不能为空', trigger: 'change'}
                    ],
                    organization_code: [
                        {required: true, message: '营业执照代码 不能为空'},
                        {max: 100, message: '营业执照代码 不能超过100个字'},
                    ],
                },
            }
        },
        methods: {
            cancel(){
                this.$emit('cancel')
            },
            resetForm(){
                this.$refs.form.resetFields();
                this.form = deepCopy(defaultForm);
            },
            save(){
                let data = deepCopy(this.form);

                if(this.data && this.data.id){
                    data.id = this.data.id;
                    data.audit_oper_id = this.data.audit_oper_id;
                }

                data.merchant_category_id = (data.merchant_category.length != 0) ? data.merchant_category[data.merchant_category.length - 1] : 0;
                data.province_id = data.area[0];
                data.city_id = data.area[1];
                data.area_id = data.area[2];
                if(data.lng_and_lat){
                    data.lng = data.lng_and_lat[0];
                    data.lat = data.lng_and_lat[1];
                }

                this.$refs.form.validate(valid => {
                    if (valid) {
                        this.$emit('save', data)
                    }
                })
            },
            selectMap(markers) {
                markers.forEach(marker => {
                    this.form.lng_and_lat = [
                        marker.getPosition().getLng(),
                        marker.getPosition().getLat(),
                    ];
                    this.$refs.lngAndLat.clearValidate();
                })
            },
            filterOperBizers(val){
                return this.operBizers.filter(item => {
                    return item.name.indexOf(val) >= 0 || item.mobile.indexOf(val) >= 0;
                })
            },
            getInitData() {
                api.get('merchant/category/getTreeWithoutDisable').then(data => {
                    this.categoryOptions = data.list;

                    let self = this;
                    for (let i = self.categoryOptions.length - 1; i >= 0; i--) {
                        if (!self.categoryOptions[i].sub) {
                            self.categoryOptions.splice(i, 1);
                        }
                    }
                });
                api.get('area/tree').then(data => {
                    this.areaOptions = data.list;
                });
                api.get('/bizer/operBizers/enable', {operId: this.data.audit_oper_id}).then(data => {
                    this.operBizers = data.list;
                })
            },
            initForm() {
                if(this.data){
                    let data = this.data;
                    data.lng = parseFloat(data.lng);
                    data.lat = parseFloat(data.lat);
                    for (let key in defaultForm){
                        this.form[key] = data[key];
                    }
                    let merchant_category_array = [];
                    if(data.merchant_category_id && data.categoryPathOnlyEnable){
                        data.categoryPathOnlyEnable.forEach(function (item) {
                            merchant_category_array.push(parseInt(item.id));
                        })
                    }
                    this.form.merchant_category = merchant_category_array;
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
            }
        },
        created(){
            this.getInitData();
            this.initForm();
        },
        watch: {

        },
        components: {
            QmapChoosePoint,
        }
    }
</script>
<style scoped>
    .title {
        line-height: 60px;
        text-align: left;
    }
</style>
