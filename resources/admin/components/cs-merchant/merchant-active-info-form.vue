<template>
    <el-form :model="form" size="small" label-width="165px" :rules="formRules" ref="form" @submit.native.prevent>

        <el-col>
            <div class="title">商户激活信息</div>
        </el-col>
        <!-- 商户激活信息左侧块 -->
        <el-col :span="11">
            <!--<el-form-item prop="bizer_id" label="签约人">
                <el-select
                        v-model="form.bizer_id"
                        filterable
                        clearable
                        placeholder="请输入业务员昵称或手机号码"
                        class="w-300"
                >
                    <el-option
                            v-for="item in operBizMembers"
                            :key="item.bizerId"
                            :label="item.bizerName + item.bizerMobile"
                            :value="item.bizerId">
                        <span class="c-blue">{{item.bizerName}}</span>
                        <span class="c-light-gray">{{item.bizerMobile}}</span>
                    </el-option>
                </el-select>
            </el-form-item>-->
            <!--<el-form-item prop="brand" label="品牌">
                <el-input v-model="form.brand"/>
            </el-form-item>-->
            <!--<el-form-item prop="invoice_title" label="发票抬头">
                <el-input v-model="form.invoice_title"/>
            </el-form-item>
            <el-form-item prop="invoice_no" label="发票税号">
                <el-input v-model="form.invoice_no"/>
            </el-form-item>-->
            <el-form-item prop="status" label="商户状态">
                <el-radio-group v-model="form.status">
                    <el-radio :label="1">正常</el-radio>
                    <el-radio :label="2">禁用</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item prop="business_time" label="营业时间">
                <el-time-picker
                        v-model="form.business_start_time"
                        placeholder="开始时间"
                />
                <span style="margin: 0 20px;">至</span>
                <el-time-picker
                        v-model="form.business_end_time"
                        placeholder="结束时间"
                />
                <!--<el-time-picker
                        is-range
                        v-model="form.business_time"
                        range-separator="至"
                        start-placeholder="开始时间"
                        end-placeholder="结束时间"
                        placeholder="选择时间范围">
                </el-time-picker>-->
            </el-form-item>
            <el-form-item prop="logo" label="商家logo">
                <image-upload :width="190" :height="190" v-model="form.logo" :limit="1"/>
                <div>图片尺寸: 190 px * 190 px</div>
            </el-form-item>
            <el-form-item prop="desc_pic_list" label="商家介绍图片">
                <image-upload :width="752" :height="398" v-model="form.desc_pic_list" :limit="6"/>
                <div>图片尺寸: 752 px * 398 px</div>
            </el-form-item>
            <el-form-item prop="desc" label="商家介绍">
                <el-input type="textarea" :rows="5" v-model="form.desc"/>
            </el-form-item>
            <!--去掉是否切换到平台限制-->
            <el-form-item prop="settlement_cycle_type" required label="结算周期">
            <!--<el-form-item v-if="isPayToPlatform" prop="settlement_cycle_type" required label="结算周期">-->
                <el-select v-model="form.settlement_cycle_type" placeholder="请选择">
                    <el-option :disabled="form.settlement_cycle_type > 1" label="周结" :value="1"/>
                    <el-option label="T+1(自动)" :value="3"/>
                    <el-option label="T+1(人工)" :value="6"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="settlement_rate" required label="分利比例">
                <el-input-number v-model="form.settlement_rate" :min="15" :max="100"/>
                <div>返利百分比,最低15%</div>
            </el-form-item>

            <!-- 银行卡信息 start -->
            <el-form-item prop="bank_card_type" label="类型">
                <el-radio-group v-model="form.bank_card_type">
                    <el-radio :label="1">公司</el-radio>
                    <el-radio :label="2">个人</el-radio>
                </el-radio-group>
                <div class="tips">请按实际账户勾选，所提供的银行账户信息须与合同一致，因勾选、输入错误等原因造成的款项结算失败所产生的错误或纠纷，由运营中心或商户各自承担</div>
            </el-form-item>
            <el-form-item prop="bank_open_name" label="银行开户名">
                <el-input v-model="form.bank_open_name"/>
            </el-form-item>
            <el-form-item prop="bank_card_no" label="银行账号">
                <el-input v-model="form.bank_card_no"/>
            </el-form-item>
            <el-form-item prop="bank_name" label="开户行">
                <el-select v-model="form.bank_name" filterable placeholder="输入银行名称关键字查找" >
                    <el-option
                            v-for="item in bankList"
                            :value="item.name"
                            :label="item.name"
                            :key="item.id"
                    ></el-option>
                </el-select>
            </el-form-item>
            <el-form-item prop="sub_bank_name" label="开户行网点名称">
                <el-input v-model="form.sub_bank_name" placeholder="填写银行网点具体名称，如北京市××分行××支行"/>
            </el-form-item>
            <el-form-item prop="bank_area" label="开户行网点地址">
                <el-cascader
                        :options="areaOptions"
                        :props="{
                            value: 'area_id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="form.bank_area">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="bank_open_address">
                <el-input v-model="form.bank_open_address" placeholder="银行网点详细地址"/>
            </el-form-item>
            <el-form-item v-if="form.bank_card_type == 1" required prop="licence_pic_url" label="开户许可证">
                <image-upload v-model="form.licence_pic_url" :limit="1"/>
            </el-form-item>
            <el-form-item v-if="form.bank_card_type == 2" required label="法人银行卡正面照" prop="bank_card_pic_a">
                <image-upload v-model="form.bank_card_pic_a" :limit="2"/>
            </el-form-item>
            <!-- 银行卡信息 end -->

            <el-form-item prop="legal_id_card_pic_a" label="法人身份证正面">
                <image-upload v-model="form.legal_id_card_pic_a" :limit="1"/>
            </el-form-item>
            <el-form-item prop="legal_id_card_pic_b" label="法人身份证反面">
                <image-upload v-model="form.legal_id_card_pic_b" :limit="1"/>
            </el-form-item>

            <el-form-item prop="corporation_name" label="法人姓名">
                <el-input v-model="form.corporation_name" placeholder="需同身份证一致"/>
            </el-form-item>
            <el-form-item label="法人身份证号码">
                <el-form-item prop="country_id" style="width: 20%; display: inline-block;">
                    <el-select v-model="form.country_id" placeholder="请选择">
                        <el-option
                                v-for="item in countryList"
                                :value="item.id"
                                :label="item.name_zh"
                                :key="item.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item prop="legal_id_card_num" style="width: 70%; display: inline-block;">
                    <el-input v-model="form.legal_id_card_num" placeholder="请输入法人身份证号码"/>
                </el-form-item>
            </el-form-item>

            <el-form-item prop="business_licence_pic_url" label="营业执照">
                <image-upload v-model="form.business_licence_pic_url" :limit="1"/>
            </el-form-item>
            <el-form-item prop="organization_code" label="营业执照代码">
                <el-input v-model="form.organization_code"/>
            </el-form-item>

            <el-form-item prop="contract_pic_url" label="合同">
                <image-upload v-model="form.contract_pic_url" :limit="10"/>
            </el-form-item>

            <el-form-item prop="other_card_pic_urls" label="其他证件">
                <image-upload v-model="form.other_card_pic_urls" :limit="10"/>
            </el-form-item>
            <el-form-item prop="audit_suggestion" label="审核意见">
                {{form.audit_suggestion}}
            </el-form-item>
        </el-col>

        <!-- 商户激活信息右侧块 -->
        <el-col :span="11" :offset="1">
            <el-form-item prop="contacter" label="负责人姓名">
                <el-input v-model="form.contacter"/>
            </el-form-item>
            <el-form-item prop="contacter_phone" label="负责人手机号码">
                <el-input v-model="form.contacter_phone"/>
            </el-form-item>
            <el-form-item prop="service_phone" label="客服电话">
                <el-input v-model="form.service_phone"/>
            </el-form-item>
            <!--<el-form-item prop="site_acreage" label="商户面积">
                <el-input v-model="form.site_acreage" placeholder="单位: ㎡"/>
            </el-form-item>-->
            <!--<el-form-item prop="employees_number" label="商户员工人数">
                <el-input v-model="form.employees_number"/>
            </el-form-item>-->
        </el-col>

    </el-form>
</template>
<script>
    import api from '../../../assets/js/api';
    import AmapChoosePoint from '../../../assets/components/amap/amap-choose-point';
    import MerchantPoolInfoForm from './merchant-pool-info-form'

    let defaultForm = {
        /////// 商户激活信息
        bizer_id: '',
        brand: '',
        status: 1,
        // business_time: [new Date('1970-01-01 00:00:00'), new Date('1970-01-01 23:59:59')],
        business_start_time: new Date('1970-01-01 00:00:00'),
        business_end_time: new Date('1970-01-01 23:59:59'),
        logo: '',
        desc_pic_list: [],
        desc: '',
        settlement_cycle_type: 1,
        settlement_rate: 0,
        // 银行卡信息
        bank_card_type: 1,
        bank_open_name: '',
        bank_card_no: '',
        bank_name: '',
        sub_bank_name: '',
        bank_area: [],
        bank_open_address: '',
        bank_card_pic_a: '',
        licence_pic_url: '',
        // 法人信息
        legal_id_card_pic_a: '',
        legal_id_card_pic_b: '',
        country_id: 1,
        corporation_name: '',
        legal_id_card_num: '',
        business_licence_pic_url: '',
        organization_code: '',
        contract_pic_url: '',
        other_card_pic_urls: '',
        audit_suggestion: '',
        // 商户负责人
        contacter: '',
        contacter_phone: '',
        service_phone: '',
        oper_salesman: '',
        site_acreage: '',
        employees_number: '',

        //是否是试点商户的字段
        is_pilot: 0,

        //////// 没有了的字段
        invoice_title: '',
        invoice_no: '',
        region: 1,
        tax_cert_pic_url: '',
        hygienic_licence_pic_url: '',
        agreement_pic_url: '',
    };
    export default {
        name: 'merchant-active-info-form',
        props: {
            data: Object,
            readonly: {type: Boolean, default: false}, // 商户激活信息是否只读
            areaOptions: Array,
        },
        computed:{

        },
        data(){
            let validateLicencePicUrl = (rule, value, callback) => {
                if(this.form.bank_card_type == 1 && value === ''){
                    callback(new Error('开户许可证 不能为空'));
                }else{
                    callback();
                }
            };
            let validateBankcardPic = (rule, value, callback) => {
                if(this.form.bank_card_type == 2 && value === ''){
                    callback(new Error('法人银行卡正面照 不能为空'));
                }else{
                    callback();
                }
            };
            let validateNumber = (rule, value, callback) => {
                if (parseFloat(value).toString() == 'NaN'){
                    callback(new Error('请输入数字'));
                } else if (value <= 0){
                    callback(new Error('输入值得大于零'))
                } else {
                    callback();
                }
            };
            let validateIdCard = (rule, value, callback) => {
                let reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                if (this.form.country_id == 2) {
                    reg = /(^((\s?[A-Za-z])|([A-Za-z]{2}))\d{6}\((([0−9aA])|([0-9aA]))\)$)/;
                } else if (this.form.country_id == 3) {
                    reg = /^[1|5|7][0-9]{6}\([0-9Aa]\)/;
                } else if (this.form.country_id == 4) {
                    reg = /^[a-zA-Z][0-9]{9}$/;
                }
                if (!(reg.test(value))) {
                    callback(new Error('请输入正确的身份证号码'));
                }else {
                    callback();
                }
            };
            let validateContacterPhone = (rule, value, callback) => {
                if (!(/^1[3,4,5,6,7,8,9]\d{9}$/.test(value))) {
                    callback(new Error('请输入正确的手机号码'));
                }else {
                    callback();
                }
            };
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    /////// 商户激活信息
                    /*invoice_title: [
                        {required: true, message: '发票抬头 不能为空'},
                    ],
                    invoice_no: [
                        {required: true, message: '发票税号 不能为空'},
                    ],*/
                    /*business_time: [
                        {type: 'array', required: true, message: '营业时间不能为空'},
                    ],*/
                    brand: [
                        {max: 20, message: '品牌名称不能超过20个字'}
                    ],
                    business_start_time: [
                        {type: 'date', required: true, message: '营业时间不能为空'},
                    ],
                    business_end_time: [
                        {type: 'date', required: true, message: '营业时间不能为空'},
                    ],
                    logo: [
                        {required: true, message: '商家logo不能为空', trigger: 'change'}
                    ],
                    desc_pic_list: [
                        {required: true, message: '商家介绍图片不能为空'}
                    ],
                    desc: [
                        {required: true, message: '商家介绍不能为空'},
                        {max: 100, message: '商家介绍不能超过100个字'}
                    ],
                    settlement_rate: [
                        {required: true, message: '分利比例不能为空'},
                    ],
                    // 银行卡信息
                    bank_open_name: [
                        {required: true, message: '银行开户名 不能为空'},
                        {max: 100, message: '银行开户名 不能超过100个字符'}
                    ],
                    bank_card_no: [
                        {required: true, message: '银行账号 不能为空'},
                        {min: 8, max: 35, message: '银行账号 8-35个数字内'}
                    ],
                    bank_name: [
                        {required: true, message: '开户行不能为空'}
                    ],
                    sub_bank_name: [
                        {required: true, message: '开户行网点名称 不能为空'},
                        {max: 100, message: '开户行网点名称 不能超过100个字'},
                    ],
                    bank_area: [
                        {type: 'array', required: true, message: '开户行网点地址不能为空'}
                    ],
                    bank_open_address: [
                        {max: 100, message: '银行网点详细地址 不能超过100个字'},
                    ],
                    licence_pic_url: [
                        {validator: validateLicencePicUrl},
                    ],
                    bank_card_pic_a: [
                        {validator: validateBankcardPic},
                    ],

                    // 法人信息
                    legal_id_card_pic_a: [
                        {required: true, message: '法人身份证照片 不能为空'},
                    ],
                    legal_id_card_pic_b: [
                        {required: true, message: '法人身份证照片 不能为空'},
                    ],
                    corporation_name: [
                        {required: true, message: '法人姓名不能为空'},
                        {max: 15, message: '法人姓名不能超过15个字'},
                    ],
                    country_id: [
                        {required: true, message:'法人身份证国别或地区 不能为空'}
                    ],
                    legal_id_card_num: [
                        {required: true, message: '法人身份证号码 不能为空'},
                        {max: 18, message: '法人身份证号码不能超过18个字'},
                        {validator: validateIdCard},
                    ],
                    business_licence_pic_url: [
                        {required: true, message: '营业执照不能为空'},
                    ],
                    organization_code: [
                        {required: true, message: '营业执照代码 不能为空'},
                        {max: 100, message: '营业执照代码 不能超过100个字'},
                    ],
                    contract_pic_url: [
                        {required: true, message: '合同照片 不能为空'},
                    ],

                    // 商户负责人
                    contacter: [
                        {required: true, message: '商户负责人姓名 不能为空'},
                        {max: 50, message: '商户负责人姓名 不能超过50个字'}
                    ],
                    contacter_phone: [
                        {required: true, message: '商户负责人手机号码 不能为空'},
                        {validator: validateContacterPhone},
                    ],
                    service_phone: [
                        {required: true, message: '客服电话 不能为空'},
                        {max: 15, message: '客服电话不能超过15个字'}
                    ],
                    oper_salesman: [
                        {required: true, message: '业务人员姓名 不能为空'},
                    ],
                    site_acreage: [
                        {required: true, message: '商户面积 不能为空'},
                        {max: 50, message: '商户面积 不能超过50个字'},
                        {validator: validateNumber}
                    ],
                    employees_number: [
                        {required: true, message: '商户员工人数 不能为空'},
                        {max: 50, message: '商户员工人数 不能超过50个字'},
                        {validator: validateNumber}
                    ],
                    settlement_cycle_type: [
                        {required: true, message: '结算周期 不能为空'},
                    ],
                },
                searchOperBizMemberLoading: false,
                operBizMembers: [],
                bankList: [],
                countryList: [],
                isPayToPlatform: false,
            }
        },
        methods: {
            getOperBizMember(){
                /*api.get('/bizer/operBizers/enable', {operId: this.data.audit_oper_id}).then(data => {
                    this.operBizMembers = data.list;
                })*/
            },
            initForm(){
                if(this.data){
                    let data = this.data;
                    for (let key in defaultForm){
                        this.form[key] = this.data[key];
                    }
                    let business_time = data.business_time;
                    this.form.business_start_time = data.business_time ? new Date('1970-01-01 '+business_time[0]) : new Date('1970-01-01 00:00:00');
                    this.form.business_end_time = data.business_time ? new Date('1970-01-01 '+business_time[1]) : new Date('1970-01-01 23:59:59');
                    if (parseInt(data.bank_province_id) == 0 && parseInt(data.bank_city_id) == 0 && parseInt(data.bank_area_id) == 0) {
                        this.form.bank_area = [];
                    }else {
                        this.form.bank_area = [parseInt(data.bank_province_id), parseInt(data.bank_city_id), parseInt(data.bank_area_id)];
                    }
                    if(data.isPayToPlatform){
                        if(parseInt(data.settlement_cycle_type) == 7){
                            this.form.settlement_cycle_type = '';
                        }else{
                            this.form.settlement_cycle_type = parseInt(data.settlement_cycle_type);
                        }
                    }
                    this.form.region = parseInt(data.region);
                    this.form.status = parseInt(data.status);
                    this.form.bank_card_type = parseInt(data.bank_card_type);
                    this.form.bizer_id = parseInt(data.bizer_id) != 0 ? parseInt(data.bizer_id) : '';
                    this.form.country_id = parseInt(data.country_id) != 0 ? parseInt(data.country_id) : 1;
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
                data.business_time = JSON.stringify([new Date(data.business_start_time).format('hh:mm:ss'), new Date(data.business_end_time).format('hh:mm:ss')]);
                data.bank_province_id = data.bank_area[0];
                data.bank_city_id = data.bank_area[1];
                data.bank_area_id = data.bank_area[2];
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
            getBankList() {
                api.get('/wallet/bank/list').then(data => {
                    this.bankList = data;
                })
            },
            getCountryList() {
                api.get('/country/list').then(data => {
                    this.countryList = data.list;
                })
            },
            getIsPayToPlatform() {
                /*api.get('/cs/merchant/isPayToPlatform',{operId: this.data.audit_oper_id}).then(data => {
                    this.isPayToPlatform = data;
                })*/
            }
        },
        created(){
            this.initForm();
            this.getOperBizMember();
            this.getBankList();
            this.getCountryList();
            this.getIsPayToPlatform();
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
            AmapChoosePoint,
            MerchantPoolInfoForm,
        }
    }
</script>
<style scoped>
    .title {
        line-height: 60px;
        text-align: left;
    }
</style>
