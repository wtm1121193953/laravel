<template>
    <page :title="title" :breadcrumbs="{商户管理: '/merchants'}">
        <el-row>
            <el-col :span="24">
                <el-form :model="form" label-width="150px" :rules="formRules" ref="form" @submit.native.prevent>
                    <el-col>
                        <div class="title">商家基本信息:</div>
                    </el-col>
                    <!--商家基本信息左侧块-->
                    <el-col :span="11">
                        <el-form-item prop="name" label="商户名称">
                            <el-input v-model="form.name"/>
                        </el-form-item>
                        <el-form-item prop="brand" label="商家品牌">
                            <el-input v-model="form.brand"/>
                        </el-form-item>
                        <el-form-item prop="region" label="运营地区">
                            <el-select v-model="form.region" placeholder="请选择">
                                <el-option label="中国" :value="1"/>
                                <el-option label="美国" :value="2"/>
                                <el-option label="韩国" :value="3"/>
                                <el-option label="香港" :value="4"/>
                            </el-select>
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
                        <el-form-item prop="business_time" label="营业时间">
                            <el-time-picker
                                    is-range
                                    v-model="form.business_time"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间"
                                    placeholder="选择时间范围">
                            </el-time-picker>
                        </el-form-item>
                        <el-form-item prop="logo" label="商家logo">
                            <image-upload :width="190" :height="190" v-model="form.logo" :limit="1"/>
                            <div>图片尺寸: 190 px * 190 px</div>
                        </el-form-item>
                        <el-form-item prop="desc_pic_list" label="商家介绍图片">
                            <image-upload :width="750" :height="526" v-model="form.desc_pic_list" :limit="6"/>
                            <div>图片尺寸: 750 px * 526 px</div>
                        </el-form-item>
                        <el-form-item prop="desc" label="商家介绍">
                            <el-input type="textarea" :rows="5" v-model="form.desc"/>
                        </el-form-item>
                    </el-col>
                    <!--商家基本信息右侧块-->
                    <el-col :span="11">
                        <el-form-item prop="invoice_title" label="发票抬头">
                            <el-input v-model="form.invoice_title"/>
                        </el-form-item>
                        <el-form-item prop="invoice_no" label="发票编号">
                            <el-input v-model="form.invoice_no"/>
                        </el-form-item>
                        <el-form-item prop="status" label="商户状态">
                            <el-radio-group v-model="form.status">
                                <el-radio :label="1">正常</el-radio>
                                <el-radio :label="2">禁用</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item prop="lng_and_lat" label="商户位置">
                            {{form.lng_and_lat}}
                            <el-button @click="isShowMap = true">更换地理位置</el-button>
                            <el-dialog title="更换地理位置" :visible.sync="isShowMap" :modal="false">
                                <amap-choose-point width="100%" height="500px" v-model="form.lng_and_lat" @select="selectMap"/>
                            </el-dialog>
                        </el-form-item>
                        <el-form-item prop="address" label="详细地址">
                            <el-input v-model="form.address"/>
                        </el-form-item>
                        <el-form-item prop="contacter" label="负责人姓名">
                            <el-input v-model="form.contacter"/>
                        </el-form-item>
                        <el-form-item prop="contacter_phone" label="客服电话">
                            <el-input v-model="form.contacter_phone"/>
                        </el-form-item>
                    </el-col>

                    <el-col>
                        <div class="title">商务信息:</div>
                    </el-col>
                    <!--商务信息左侧块-->
                    <el-col :span="11">

                        <el-form-item prop="settlement_cycle_type" label="结算周期">
                            <el-select :disabled="!!data" v-model="form.settlement_cycle_type" placeholder="请选择">
                                <el-option label="周结" :value="1"/>
                                <el-option label="半月结" :value="2"/>
                                <el-option label="月结" :value="3"/>
                                <el-option label="半年结" :value="4"/>
                                <el-option label="年结" :value="5"/>
                            </el-select>
                        </el-form-item>
                        <el-form-item prop="settlement_rate" label="分利比例">
                            <el-input-number v-model="form.settlement_rate" :min="0" :max="100"/>
                            <div>返利百分比,如20%请填写20</div>
                        </el-form-item>
                        <el-form-item prop="business_licence_pic_url" label="营业执照（必填）">
                            <image-upload v-model="form.business_licence_pic_url" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="organization_code" label="组织机构代码">
                            <el-input v-model="form.organization_code"/>
                        </el-form-item>
                        <el-form-item prop="tax_cert_pic_url" label="税务登记证">
                            <image-upload v-model="form.tax_cert_pic_url" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="legal_id_card_pic_a" label="法人身份证正面">
                            <image-upload v-model="form.legal_id_card_pic_a" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="legal_id_card_pic_b" label="法人身份证反面">
                            <image-upload v-model="form.legal_id_card_pic_b" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="contract_pic_url" label="合同">
                            <image-upload v-model="form.contract_pic_url" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="licence_pic_url" label="开户许可证">
                            <image-upload v-model="form.licence_pic_url" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="hygienic_licence_pic_url" label="卫生许可证">
                            <image-upload v-model="form.hygienic_licence_pic_url" :limit="1"/>
                        </el-form-item>
                        <el-form-item prop="agreement_pic_url" label="协议文件">
                            <image-upload v-model="form.agreement_pic_url" :limit="1"/>
                        </el-form-item>
                    </el-col>
                    <!--商务信息右侧块-->
                    <el-col :span="11">

                        <el-form-item prop="bank_card_type" label="类型">
                            <el-radio-group v-model="form.bank_card_type">
                                <el-radio :label="1">公司</el-radio>
                                <el-radio :label="2">个人</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item prop="bank_open_name" label="银行开户名">
                            <el-input v-model="form.bank_open_name"/>
                        </el-form-item>
                        <el-form-item prop="bank_card_no" label="银行账号">
                            <el-input v-model="form.bank_card_no"/>
                        </el-form-item>
                        <el-form-item prop="sub_bank_name" label="开户支行名称">
                            <el-input v-model="form.sub_bank_name"/>
                        </el-form-item>
                        <el-form-item prop="bank_open_address" label="开户支行地址">
                            <el-input v-model="form.bank_open_address"/>
                        </el-form-item>
                    </el-col>
                    <el-col>
                        <el-form-item>
                            <el-button @click="cancel">取消</el-button>
                            <el-button type="primary" @click="save">保存</el-button>
                        </el-form-item>
                    </el-col>
                </el-form>
            </el-col>
        </el-row>
    </page>
</template>
<script>
    import api from '../../../assets/js/api';
    import AmapChoosePoint from '../../../assets/components/amap/amap-choose-point';

    let defaultForm = {
        name: '',
        brand: '',
        region: 1,
        merchant_category: [],
        area: [],
        business_time: [new Date('1970-01-01 00:00:00'), new Date('1970-01-01 23:59:59')],
        logo: '',
        desc_pic_list: [],
        desc: '',
        invoice_title: '',
        invoice_no: '',
        status: 1,
        lng_and_lat: null,
        address: '',
        contacter: '',
        contacter_phone: '',

        settlement_cycle_type: 1,
        settlement_rate: 0,
        business_licence_pic_url: '',
        organization_code: '',
        tax_cert_pic_url: '',
        legal_id_card_pic_a: '',
        legal_id_card_pic_b: '',
        contract_pic_url: '',
        licence_pic_url: '',
        hygienic_licence_pic_url: '',
        agreement_pic_url: '',
        bank_card_type: 1,
        bank_open_name: '',
        bank_card_no: '',
        sub_bank_name: '',
        bank_open_address: '',
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
                    logo: [
                        {required: true, message: '商家logo不能为空', trigger: 'change'}
                    ],
                    desc_pic_list: [
                        {required: true, message: '商家介绍图片不能为空'}
                    ],
                    desc: [
                        {required: true, message: '商家介绍不能为空'}
                    ],
                    settlement_rate: [
                        {required: true, message: '分利比例不能为空'},
                        {
                            validator(rule, value, callback){
                                if(value === ''){
                                    callback(new Error('分利比例不能为空'));
                                }else{
                                    callback();
                                }
                            }
                        }
                    ],
                    business_licence_pic_url: [
                        {required: true, message: '营业执照不能为空'},
                    ],
                    lng_and_lat: [
                        {required: true, message: '商户位置不能为空'},
                    ],
                    area: [
                        {type: 'array', required: true, message: '省/市/区不能为空'},
                    ],
                    contacter_phone: [
                        {required: true, message: '客服电话不能为空'},
                    ],
                    business_time: [
                        {type: 'array', required: true, message: '营业时间不能为空'},
                    ]
                },
            }
        },
        methods: {
            initForm(){
                api.get('merchant/categories/tree').then(data => {
                    this.categoryOptions = data.list;
                })
                api.get('area/tree').then(data => {
                    this.areaOptions = data.list;
                })
                if(this.data){
                    let data = this.data;
                    this.form = deepCopy(data)
                    let merchant_category_array = [];
                    if(data.merchant_category_id){
                        data.categoryPath.forEach(function (item) {
                            merchant_category_array.unshift(parseInt(item.id));
                        })
                    }

                    this.form.merchant_category = merchant_category_array;
                    this.form.area = [parseInt(data.province_id), parseInt(data.city_id), parseInt(data.area_id)];
                    this.form.business_time = data.business_time ? ['1970-01-01 '+JSON.parse(data.business_time)[0], '1970-01-01 '+JSON.parse(data.business_time)[1]] : [new Date('1970-01-01 00:00:00'), new Date('1970-01-01 23:59:59')];
                    this.form.lng_and_lat = [data.lng, data.lat];
                    this.form.region = parseInt(data.region);
                    this.form.settlement_cycle_type = parseInt(data.settlement_cycle_type);
                    this.form.status = parseInt(data.status);
                    this.form.bank_card_type = parseInt(data.bank_card_type);
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
                        data.business_time = JSON.stringify([new Date(data.business_time[0]).format('hh:mm:ss'), new Date(data.business_time[1]).format('hh:mm:ss')]);
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
        font-weight: 600;
        line-height: 50px;
    }
</style>
