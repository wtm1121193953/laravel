<template>
    <page title="提现设置">
        <el-form v-loading="formLoading" label-width="150px">
            <el-form-item label="提现银行卡号">
                {{form.bank_card_no}}
            </el-form-item>
            <el-form-item label="开户行">
                {{form.bank_name}}
            </el-form-item>
            <el-form-item label="账户姓名">
                {{form.bank_card_open_name}}
            </el-form-item>
            <el-form-item label="身份证号码">
                {{form.id_card_no}}
            </el-form-item>
            <el-form-item label="身份证正面">
                <div v-viewer>
                    <img :src="form.front_pic" alt="身份证正面" width="200px" height="100%" />
                </div>
            </el-form-item>
            <el-form-item label="身份证反面">
                <div v-viewer>
                    <img :src="form.opposite_pic" alt="身份证反面" width="200px" height="100%" />
                </div>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" size="small" @click="showBankCardDialog = true">修改银行卡</el-button>
                <el-button type="primary" size="small" @click="changeWithdrawPassword">修改提现密码</el-button>
            </el-form-item>
        </el-form>

        <el-dialog title="修改银行卡" center :visible.sync="showBankCardDialog" width="20%">
            <el-form :model="form2" :rules="formRules2" ref="form2" size="small" label-width="110px">
                <el-form-item prop="bank_card_no" label="提现银行卡号">
                    <el-input v-model="form2.bank_card_no" clearable/>
                </el-form-item>
                <el-form-item prop="bank_name" label="开户行">
                    <el-select v-model="form2.bank_name" filterable clearable placeholder="请选择">
                        <el-option
                                v-for="item in bankList"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item prop="bank_card_open_name" label="账户姓名">
                    {{form.bank_card_open_name}}
                </el-form-item>
                <el-form-item>
                    <el-button size="small" @click="showBankCardDialog = false">暂不修改</el-button>
                    <el-button size="small" type="primary" @click="changeBankCardNo">确定修改</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>

        <el-dialog
                center
                title="忘记密码"
                :visible.sync="showForgetPasswordDialog"
                :modal-append-to-body="false"
                :append-to-body="true"
                :close-on-click-modal="false"
                :close-on-press-escape="false"
                @close="resetVerifyCodeForm"
                width="20%"
        >
            <el-form :model="verifyCodeForm" ref="verifyCodeForm" :rules="verifyCodeFormRules" size="small" label-width="65px">
                <el-form-item prop="mobile" label="帐号">
                    <el-input v-model="verifyCodeForm.mobile" placeholder="请输入手机号" style="width: 80%;"/>
                </el-form-item>
                <el-form-item prop="verifyCode" label="验证码">
                    <el-input v-model="verifyCodeForm.verifyCode" placeholder="请输入验证码" style="width: 50%;"/>
                    <span><el-button type="primary" :disabled="isDisabled" @click="getVerifyCode" style="padding: 8px 7px">{{buttonName}}</el-button></span>
                </el-form-item>
                <div style="text-align: center">
                    <el-button size="small" type="primary" @click="checkVerifyCode">确定</el-button>
                </div>
            </el-form>
        </el-dialog>

        <el-dialog
                center
                title="设置提现密码"
                :visible.sync="showSetPasswordDialog"
                :modal-append-to-body="false"
                :append-to-body="true"
                :close-on-click-modal="false"
                :close-on-press-escape="false"
                @close="resetSetPasswordForm"
                width="20%"
        >
            <el-form :model="setPasswordForm" ref="setPasswordForm" :rules="setPasswordFormRules" size="small" label-width="100px">
                <el-form-item prop="password" label="提现密码">
                    <el-input v-model="setPasswordForm.password" :maxlength="6" auto-complete="off" type="password" placeholder="请输入提现密码" class="w-200"/>
                </el-form-item>
                <el-form-item prop="checkPassword" label="确认提现密码">
                    <el-input v-model="setPasswordForm.checkPassword" :maxlength="6" auto-complete="off" type="password" placeholder="请确认提现密码" class="w-200"/>
                </el-form-item>
                <div style="text-align: center">
                    <el-button size="small" type="primary" @click="setPassword">确定</el-button>
                </div>
            </el-form>
        </el-dialog>

    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import 'viewerjs/dist/viewer.css'

    export default {
        name: "password-success",
        data() {
            let validateBankCardNo = (rule, value, callback) => {
                if (!(/^[0-9]\d{0,34}$/.test(value))) {
                    callback(new Error('请输入正确的提现银行卡号'));
                } else {
                    callback();
                }
            };
            let validateMobile = (rule, value, callback) => {
                let reg=/^[1][34578][0-9]{9}$/;
                if (!reg.test(value)) {
                    callback(new Error('请输入正确的手机号码'));
                } else {
                    callback();
                }
            };
            let validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                } else if (value.length !== 6) {
                    callback(new Error('密码需为6位'));
                } else {
                    if (this.setPasswordForm.checkPassword !== '') {
                        this.$refs.setPasswordForm.validateField('checkPassword');
                    }
                    callback();
                }
            };
            let validateCheckPass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                }  else if (value !== this.setPasswordForm.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };

            return {
                formLoading: false,
                form: {
                    bank_card_no: '',
                    bank_name: '',
                    bank_card_open_name: '',
                    id_card_no: '',
                    front_pic: '',
                    opposite_pic: '',
                },
                bankList: [],
                showBankCardDialog: false,
                form2: {
                    bank_card_no: '',
                    bank_name: '',
                },
                formRules2: {
                    bank_card_no: [
                        {required: true, message: '提现银行卡号不能为空'},
                        {max: 35, min: 8, message: '提现银行卡号最少为8位，最多为35位'},
                        {validator: validateBankCardNo}
                    ],
                    bank_name: [
                        {required: true, message: '开户行不能为空'},
                    ],
                },
                initMobile: '',

                showForgetPasswordDialog: false,
                verifyCodeForm: {
                    mobile: '',
                    verifyCode: '',
                },
                buttonName: '获取验证码',
                isDisabled: false,
                time: 60,

                showSetPasswordDialog: false,
                setPasswordForm: {
                    password: '',
                    checkPassword: '',
                },
                verifyCodeFormRules: {
                    mobile: [
                        {required: true, message: '帐号不能为空'},
                        {validator: validateMobile}
                    ],
                    verifyCode: [
                        {required: true, message: '验证码不能为空'},
                        {max: 4, message: '验证码不能超过4位'},
                    ]
                },
                setPasswordFormRules: {
                    password: [
                        {validator: validatePass}
                    ],
                    checkPassword: [
                        {validator: validateCheckPass}
                    ]
                }
            }
        },
        methods: {
            getInfo() {
                this.formLoading = true;
                api.get('/wallet/withdraw/getBankCardAndIdCardInfo').then(data => {
                    this.form.bank_card_no = data.bankCard.bank_card_no;
                    this.form.bank_name = data.bankCard.bank_name;
                    this.form.bank_card_open_name = data.bankCard.bank_card_open_name;
                    this.form.id_card_no = data.identityAuditRecord.id_card_no;
                    this.form.front_pic = data.identityAuditRecord.front_pic;
                    this.form.opposite_pic = data.identityAuditRecord.opposite_pic;
                    this.initMobile = data.bizer.mobile;
                    this.formLoading = false;
                });
            },
            getBankList() {
                api.get('/wallet/bank/list').then(data => {
                    this.bankList = data;
                })
            },
            changeBankCardNo() {
                this.$refs.form2.validate(valid => {
                    if (valid) {
                        api.post('/wallet/withdraw/editBizerBankCard', this.form2).then(data => {
                            this.form.bank_card_no = data.bank_card_no;
                            this.form.bank_name = data.bank_name;
                            this.showBankCardDialog = false;
                            this.$refs.form2.resetFields();
                        })
                    }
                })
            },
            changeWithdrawPassword() {
                this.showForgetPasswordDialog = true;
            },
            getVerifyCode() {
                let self = this;

                let reg=/^[1][34578][0-9]{9}$/;
                if (!reg.test(self.verifyCodeForm.mobile)) {
                    self.$message.error('请输入有效手机号码');
                    return false;
                } else if (self.verifyCodeForm.mobile != this.initMobile) {
                    self.$message.error('该手机号与业务员注册手机号不符');
                    return;
                } else {
                    self.isDisabled = true;
                    let interval = window.setInterval(function() {
                        self.buttonName = '（' + self.time + '秒）后重新发送';
                        --self.time;
                        if(self.time < 0) {
                            self.buttonName = "获取验证码";
                            self.time = 60;
                            self.isDisabled = false;
                            window.clearInterval(interval);
                        }
                    }, 1000);

                    //获取验证码
                    api.get('sms/getVerifyCode', {mobile: self.verifyCodeForm.mobile}).then(() => {
                        self.$message.success('发送成功');
                    })
                }
            },
            checkVerifyCode() {
                if (this.verifyCodeForm.mobile != this.initMobile) {
                    this.$message.error('该手机号与业务员注册手机号不符');
                    return;
                }
                this.$refs.verifyCodeForm.validate(valid => {
                    if (valid) {
                        api.post('/sms/checkVerifyCode', this.verifyCodeForm).then(() => {
                            this.showForgetPasswordDialog = false;
                            this.showSetPasswordDialog = true;
                        });
                    }
                });
            },
            resetVerifyCodeForm() {
                this.$refs.verifyCodeForm.resetFields();
            },
            resetSetPasswordForm() {
                this.$refs.setPasswordForm.resetFields();
            },
            setPassword() {
                this.$refs.setPasswordForm.validate(valid => {
                    if (valid) {
                        api.post('/wallet/withdraw/resetWithdrawPassword', this.setPasswordForm).then(() => {
                            this.$message.success('修改提现密码成功');
                            this.showSetPasswordDialog = false;
                        })
                    }
                })
            },
        },
        created() {
            this.getInfo();
            this.getBankList();
        }
    }
</script>

<style scoped>

</style>