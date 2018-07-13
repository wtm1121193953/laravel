<template>
    <el-form :model="form" size="small" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>

        <el-col>
            <div class="title">商户激活信息</div>
        </el-col>
        <!-- 商户激活信息左侧块 -->
        <el-col :span="11">
            <el-form-item prop="oper_biz_member_code" label="业务员">
                <el-select
                        v-model="form.oper_biz_member_code"
                        filterable
                        remote
                        reserve-keyword
                        clearable
                        placeholder="请输入业务员姓名或手机号码"
                        :remote-method="searchOperBizMember"
                        :loading="searchOperBizMemberLoading"
                        @clear="resetCode"
                        class="w-300"
                >
                    <el-option
                            v-for="item in operBizMembers"
                            :key="item.id"
                            :label="item.name"
                            :value="item.code">
                        <!--<span class="c-gray">{{item.code}}</span>-->
                        <span class="c-blue">{{item.name}}</span>
                        <span class="c-light-gray">{{item.mobile}}</span>
                    </el-option>
                </el-select>
            </el-form-item>
            <!--<el-form-item prop="brand" label="品牌">
                <el-input v-model="form.brand"/>
            </el-form-item>-->
            <el-form-item prop="signboard_name" label="招牌名称">
                <el-input v-model="form.signboard_name"/>
            </el-form-item>
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
                <image-upload :width="190" :height="190" v-model="form.logo" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="1"/>
                <div>图片尺寸: 190 px * 190 px</div>
            </el-form-item>
            <el-form-item prop="desc_pic_list" label="商家介绍图片">
                <image-upload :width="750" :height="526" v-model="form.desc_pic_list" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="6"/>
                <div>图片尺寸: 750 px * 526 px</div>
            </el-form-item>
            <el-form-item prop="desc" label="商家介绍">
                <el-input type="textarea" :rows="5" v-model="form.desc"/>
            </el-form-item>
            <el-form-item prop="settlement_cycle_type" required label="结算周期">
                <el-select :disabled="!!data && data.audit_oper_id != 0" v-model="form.settlement_cycle_type" placeholder="请选择">
                    <el-option label="周结" :value="1"/>
                    <!--<el-option label="半月结" :value="2"/>
                    <el-option label="月结" :value="3"/>
                    <el-option label="半年结" :value="4"/>
                    <el-option label="年结" :value="5"/>-->
                </el-select>
            </el-form-item>
            <el-form-item prop="settlement_rate" required label="分利比例">
                <el-input-number v-model="form.settlement_rate" :min="0" :max="100"/>
                <div>返利百分比,如20%请填写20</div>
            </el-form-item>

            <!-- 银行卡信息 start -->
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
            <el-form-item v-if="form.bank_card_type == 1" required prop="licence_pic_url" label="开户许可证">
                <image-upload v-model="form.licence_pic_url" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="1"/>
            </el-form-item>
            <el-form-item v-if="form.bank_card_type == 2" required label="法人银行卡正面照" prop="bank_card_pic_a">
                <image-upload v-model="form.bank_card_pic_a" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="2"/>
            </el-form-item>
            <!-- 银行卡信息 end -->

            <el-form-item prop="legal_id_card_pic_a" label="法人身份证正面">
                <image-upload v-model="form.legal_id_card_pic_a" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="1"/>
            </el-form-item>
            <el-form-item prop="legal_id_card_pic_b" label="法人身份证反面">
                <image-upload v-model="form.legal_id_card_pic_b" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="1"/>
            </el-form-item>

            <el-form-item prop="business_licence_pic_url" label="营业执照">
                <image-upload v-model="form.business_licence_pic_url" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="1"/>
            </el-form-item>
            <el-form-item prop="organization_code" label="营业执照代码">
                <el-input v-model="form.organization_code"/>
            </el-form-item>

            <el-form-item prop="contract_pic_url" label="合同">
                <image-upload v-model="form.contract_pic_url" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="10"/>
            </el-form-item>

            <el-form-item prop="other_card_pic_urls" label="其他证件">
                <image-upload v-model="form.other_card_pic_urls" @before="plusUpload" @complete="descUpload" @error="descUpload" :limit="10"/>
            </el-form-item>
        </el-col>

        <!-- 商户激活信息右侧块 -->
        <el-col :span="11" :offset="1">
            <el-form-item prop="contacter" label="负责人姓名">
                <el-input v-model="form.contacter"/>
            </el-form-item>
            <el-form-item prop="contacter_phone" label="负责人联系方式">
                <el-input v-model="form.contacter_phone"/>
            </el-form-item>
            <el-form-item prop="service_phone" label="客服电话">
                <el-input v-model="form.service_phone"/>
            </el-form-item>
            <el-form-item prop="site_acreage" label="商户面积">
                <el-input v-model="form.site_acreage" placeholder="单位: ㎡"/>
            </el-form-item>
            <el-form-item prop="employees_number" label="商户员工人数">
                <el-input v-model="form.employees_number"/>
            </el-form-item>
        </el-col>

    </el-form>
</template>
<script>
    import api from '../../../assets/js/api';
    import AmapChoosePoint from '../../../assets/components/amap/amap-choose-point';
    import MerchantPoolInfoForm from './merchant-pool-info-form'

    let defaultForm = {
        /////// 商户激活信息
        oper_biz_member_code: '',
        signboard_name: '',
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
        sub_bank_name: '',
        bank_open_address: '',
        bank_card_pic_a: '',
        licence_pic_url: '',
        // 法人信息
        legal_id_card_pic_a: '',
        legal_id_card_pic_b: '',
        business_licence_pic_url: '',
        organization_code: '',
        contract_pic_url: '',
        other_card_pic_urls: '',
        // 商户负责人
        contacter: '',
        contacter_phone: '',
        service_phone: '',
        oper_salesman: '',
        site_acreage: '',
        employees_number: '',

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
                    signboard_name: [
                        {required: true, message: '招牌名称不能为空'},
                        {max: 20, message: '招牌名称不能超过20个字'}
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

                    // 银行卡信息
                    bank_open_name: [
                        {required: true, message: '开户名 不能为空'},
                    ],
                    bank_card_no: [
                        {required: true, message: '银行账号 不能为空'},
                    ],
                    sub_bank_name: [
                        {required: true, message: '开户支行名称 不能为空'},
                    ],
                    bank_open_address: [
                        {required: true, message: '开户支行地址 不能为空'},
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
                    business_licence_pic_url: [
                        {required: true, message: '营业执照不能为空'},
                    ],
                    organization_code: [
                        {required: true, message: '营业执照代码不能为空'},
                    ],
                    contract_pic_url: [
                        {required: true, message: '合同照片 不能为空'},
                    ],

                    // 商户负责人
                    contacter: [
                        {required: true, message: '商户负责人姓名 不能为空'},
                    ],
                    contacter_phone: [
                        {required: true, message: '商户负责人联系方式 不能为空'},
                        {max: 15, message: '商户负责人联系方式不能超过15个字'}
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
                        {validator: validateNumber}
                    ],
                    employees_number: [
                        {required: true, message: '商户员工人数 不能为空'},
                        {validator: validateNumber}
                    ],
                },
                searchOperBizMemberLoading: false,
                operBizMembers: [],
                uploadVoucher: 0,
            }
        },
        methods: {
            searchOperBizMember(query){
                if (query !== '') {
                    this.searchOperBizMemberLoading = true;
                    api.get('/operBizMembers/search', {keyword: query, status: 1}).then(data => {
                        this.operBizMembers = data.list;
                    }).finally(() => {
                        this.searchOperBizMemberLoading = false;
                    })
                } else {
                    this.operBizMembers = [];
                }
            },
            initForm(){
                if(this.data){
                    let data = this.data;
                    for (let key in defaultForm){
                        this.form[key] = this.data[key];
                    }
                    // this.form.business_time = data.business_time
                    //     ? ['1970-01-01 '+JSON.parse(data.business_time)[0], '1970-01-01 '+JSON.parse(data.business_time)[1]]
                    //     : [new Date('1970-01-01 00:00:00'), new Date('1970-01-01 23:59:59')];
                    let business_time = JSON.parse(data.business_time);
                    this.form.business_start_time = data.business_time ? new Date('1970-01-01 '+business_time[0]) : new Date('1970-01-01 00:00:00');
                    this.form.business_end_time = data.business_time ? new Date('1970-01-01 '+business_time[1]) : new Date('1970-01-01 23:59:59');
                    console.log(this.form.business_start_time, this.form.business_end_time)
                    this.form.region = parseInt(data.region);
                    this.form.settlement_cycle_type = parseInt(data.settlement_cycle_type);
                    this.form.status = parseInt(data.status);
                    this.form.bank_card_type = parseInt(data.bank_card_type);
                    this.searchOperBizMember(this.form.oper_biz_member_code);
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
                if (this.uploadVoucher == 0){
                    let data = deepCopy(this.form);
                    if(this.data && this.data.id){
                        data.id = this.data.id;
                    }
                    data.business_time = JSON.stringify([new Date(data.business_start_time).format('hh:mm:ss'), new Date(data.business_end_time).format('hh:mm:ss')]);
                    return data;
                } else {
                    this.$message.warning('图片上传中, 请稍后重试');
                    return false;
                }
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
            resetCode() {
                this.form.oper_biz_member_code = '';
                this.operBizMembers = [];
            },
            plusUpload() {
                this.uploadVoucher ++ ;
            },
            descUpload() {
                this.uploadVoucher -- ;
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
