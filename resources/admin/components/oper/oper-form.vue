<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="150px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="运营中心名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="number" label="运营中心编码">
                    <el-input v-model="form.number"/>
                </el-form-item>
                <el-form-item prop="contacter" label="负责人">
                    <el-input v-model="form.contacter" placeholder=""/>
                </el-form-item>
                <el-form-item prop="tel" label="手机号码">
                    <el-input v-model="form.tel" placeholder=""/>
                </el-form-item>
                <el-form-item prop="selectAreas" label="城市">
                    <el-cascader
                            :options="areas"
                            :props="{value: 'area_id', label: 'name', children: 'sub'}"
                            v-model="form.selectAreas">
                    </el-cascader>
                </el-form-item>
                <el-form-item prop="address" label="详细地址">
                    <el-input v-model="form.address" placeholder=""/>
                </el-form-item>
                <el-form-item prop="email" label="邮箱">
                    <el-input v-model="form.email" placeholder=""/>
                </el-form-item>
                <el-form-item prop="legal_name" label="法人姓名">
                    <el-input v-model="form.legal_name" placeholder=""/>
                </el-form-item>
                <el-form-item label="法人身份证" prop="legal_id_card">
                    <el-input v-model="form.legal_id_card" placeholder=""/>
                </el-form-item>
                <el-form-item label="发票类型" prop="invoice_type">
                    <el-select v-model="form.invoice_type" placeholder="">
                        <el-option
                                v-for="item in invoiceTypes"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="发票税点" prop="invoice_tax_rate">
                    <el-input-number v-model="form.invoice_tax_rate" :min="0" :max="100"/>
                </el-form-item>
                <!--<el-form-item label="结款周期" prop="settlement_cycle_type">
                    <el-select v-model="form.settlement_cycle_type" placeholder="">
                        <el-option
                                v-for="item in settlementCycles"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>-->
                <el-form-item label="公司账号" prop="bank_card_no">
                    <el-input v-model="form.bank_card_no" placeholder=""/>
                </el-form-item>
                <el-form-item label="开户支行名称" prop="sub_bank_name">
                    <el-input v-model="form.sub_bank_name" placeholder=""/>
                </el-form-item>
                <el-form-item label="开户名" prop="bank_open_name">
                    <el-input v-model="form.bank_open_name" placeholder=""/>
                </el-form-item>
                <el-form-item label="开户地址" prop="bank_open_address">
                    <el-input v-model="form.bank_open_address" placeholder=""/>
                </el-form-item>
                <el-form-item label="银行代码" prop="bank_code">
                    <el-input v-model="form.bank_code" placeholder=""/>
                </el-form-item>
                <el-form-item label="开户许可证" prop="licence_pic_url">
                    <image-upload v-model="form.licence_pic_url" :limit="1"/>
                </el-form-item>
                <el-form-item label="营业执照" prop="business_licence_pic_url">
                    <image-upload v-model="form.business_licence_pic_url" :limit="1"/>
                </el-form-item>
                <el-form-item label="合作状态" prop="status">
                    <el-select v-model="form.status" placeholder="">
                        <el-option label="正常合作中" :value="1"/>
                        <el-option label="已冻结" :value="2"/>
                        <el-option label="停止合作" :value="3"/>
                    </el-select>
                </el-form-item>

                <el-form-item label="客服联系方式-QQ" prop="contact_qq">
                    <el-input v-model="form.contact_qq" placeholder=""/>
                </el-form-item>
                <el-form-item label="客服联系方式-微信" prop="contact_wechat">
                    <el-input v-model="form.contact_wechat" placeholder=""/>
                </el-form-item>
                <el-form-item label="客服联系方式-手机" prop="contact_mobile">
                    <el-input v-model="form.contact_mobile" placeholder=""/>
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
    let defaultForm = {
        name: '',
        number:'',
        status: 1,
        contacter: '',
        tel: '',
        selectAreas: null,
        address: '',
        email: '',
        legal_name: '',
        legal_id_card: '',
        invoice_type: 1,
        invoice_tax_rate: '',
        // settlement_cycle_type: 2,
        bank_card_no: '',
        sub_bank_name: '',
        bank_open_name: '',
        bank_open_address: '',
        bank_code: '',
        licence_pic_url: '',
        business_licence_pic_url: '',
        contact_qq: '',
        contact_wechat: '',
        contact_mobile: '',
    };
    export default {
        name: 'oper-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            let validateContacterPhone = (rule, value, callback) => {
                if (!(/^1[3,4,5,6,7,8,9]\d{9}$/.test(value))) {
                    callback(new Error('请输入正确的手机号码'));
                }else {
                    callback();
                }
            };
            let validateIdCard = (rule, value, callback) => {
                if (!(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(value))) {
                    callback(new Error('请输入正确的身份证号码'));
                }else {
                    callback();
                }
            };
            let validateBankCardNo = (rule, value, callback) => {
                if (!(/^[0-9]\d{0,35}$/.test(value))) {
                    callback(new Error('请输入正确的公司账号'));
                } else {
                    callback();
                }
            };
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'},
                        {max: 50, message: '运营中心名称不能超过50个字'},
                    ],
                    number: [
                        {max: 20, message: '运营中心编码不能超过20个字'},
                    ],
                    selectAreas: [
                        {required: true, type: 'array', message: '地区不能为空' }
                    ],
                    tel: [
                        {required: true, message: '手机号码不能为空'},
                        {validator: validateContacterPhone}
                    ],
                    email: [
                        {type: 'email', message: '请输入正确的邮箱'},
                    ],
                    legal_id_card: [
                        {validator: validateIdCard}
                    ],
                    bank_card_no: [
                        {validator: validateBankCardNo},
                        {required: true, message: '银行账号 不能为空'},
                        {min: 8, max: 35, message: '银行账号 8-35个数字内'}
                    ],
                    contacter: [
                        {max: 60, message: '负责人不能超过60个字'}
                    ],
                    address: [
                        {max: 60, message: '详细地址不能超过60个字'}
                    ],
                    legal_name: [
                        {max: 60, message: '法人姓名不能超过60个字'}
                    ],
                    contact_qq: [
                        {required: true, message: '客服联系方式-QQ 不能为空'},
                        {max: 50, message: '客服联系方式-QQ 不能超过50个字'}
                    ],
                    contact_wechat: [
                        {required: true, message: '客服联系方式-微信 不能为空'},
                        {max: 50, message: '客服联系方式-微信 不能超过50个字'}
                    ],
                    contact_mobile: [
                        {required: true, message: '客服联系方式-手机 不能为空'},
                        {validator: validateContacterPhone}
                    ],
                },
                areas: [],
                invoiceTypes: [
                    {value: 1, label: '增值税普票'},
                    {value: 2, label: '增值税专票'},
                    {value: 3, label: '国税普票'},
                    {value: 0, label: '其他'},
                ],
                settlementCycles: [
                    {value: 1, label: '周结'},
                    {value: 2, label: '半月结'},
                    {value: 3, label: '月结'},
                    {value: 4, label: '半年结'},
                    {value: 5, label: '年结'},
                ],
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data);
                    this.form.selectAreas = [this.data.province_id, this.data.city_id];
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
                        data.province_id = data.selectAreas[0];
                        data.city_id = data.selectAreas[1];
                        this.$emit('save', data);
                    }
                })

            },
            getAreaTree(){
                api.get('area/tree', {tier: 2}).then(data => {
                    this.areas = data.list;
                })
            }
        },
        created(){
            this.initForm();
            this.getAreaTree();
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
