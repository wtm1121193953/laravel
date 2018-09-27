<template>
    <el-row :gutter="20" v-loading="formLoading">
        <el-form :model="form" :rules="formRules" ref="form" label-width="100px" label-position="left" size="small">
            <el-col :span="12">
                <el-form-item label="可提现金额">
                    {{initForm.balance}}
                </el-form-item>
                <el-form-item prop="amount" label="提现金额">
                    <el-input-number v-model="form.amount" :precision="2" :min="0" :max="parseFloat(initForm.balance)"/>
                    <span>手续费{{chargeAmount}}</span>
                </el-form-item>
                <el-form-item prop="withdrawPassword" label="提现密码">
                    <el-input v-model="form.withdrawPassword" type="password" :maxlength="6" placeholder="6位纯数字密码" class="w-150"/>
                    <span><i class="el-icon-question"></i><el-button type="text" @click="forgetPassword">忘记密码</el-button></span>
                </el-form-item>
                <el-form-item label="提现账户名">
                    {{initForm.bankCardOpenName}}
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="提现银行卡号">
                    {{initForm.bankCardNo}}
                </el-form-item>
                <el-form-item label="开户行">
                    {{initForm.bankName}}
                </el-form-item>
                <el-form-item label="到账金额">
                    {{remitAmount}}
                </el-form-item>
            </el-col>
            <el-col class="tips">温馨提示： 每月10号、20号、30号才能提现</el-col>
            <el-col style="text-align: center">
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="commit">立即申请</el-button>
            </el-col>
        </el-form>

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
                <el-form-item prop="mobile" label="账号">
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
    </el-row>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data() {
            let validateWithdrawPassword = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入提现密码'));
                } else if (isNaN(value)) {
                    callback(new Error('请输入数字值'));
                } else {
                    callback();
                }
            };
            let validateAmount = (rule, value, callback) => {
                if (value < 100) {
                    callback(new Error('提现金不能小于100'));
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
                initForm: {
                    balance: 0,
                    bankCardOpenName: '',
                    bankCardNo: '',
                    bankName: '',
                    bizerMobile: '',
                    ratio: 0,  // 手续费百分比
                },
                form: {
                    amount: 0,
                    withdrawPassword: '',
                },
                formLoading: false,
                noWithdraw: true,

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

                formRules: {
                    amount: [
                        {validator: validateAmount}
                    ],
                    withdrawPassword: [
                        {validator: validateWithdrawPassword}
                    ],
                },
                verifyCodeFormRules: {
                    mobile: [
                        {required: true, message: '账号不能为空'},
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
        computed: {
            //手续费
            chargeAmount() {
                if (this.form.amount == undefined) this.form.amount = 0;
                return (this.form.amount * this.initForm.ratio / 100).toFixed(2);
            },
            //到账金额
            remitAmount() {
                if (this.form.amount == undefined) this.form.amount = 0;
                return (this.form.amount * (1 - this.initForm.ratio / 100)).toFixed(2);
            },
        },
        methods: {
            init() {
                this.formLoading = true;
                api.get('/wallet/withdraw/getWithdrawInfoAndBankInfo').then(data => {
                    this.initForm = data;
                    this.formLoading = false;
                })
            },
            cancel() {
                this.$emit('closeWithdrawDialog');
                this.$refs.form.resetFields();
            },
            commit() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        api.post('/wallet/withdraw/withdrawApplication', this.form).then(data => {
                            this.$message.success('提现申请成功');
                            this.$emit('closeWithdrawDialog');
                            this.$emit('getList');
                            this.$refs.form.resetFields();
                        })
                    }
                })
            },
            forgetPassword() {
                this.showForgetPasswordDialog = true;
            },
            getVerifyCode() {
                let self = this;

                let reg=/^[1][34578][0-9]{9}$/;
                if (!reg.test(self.verifyCodeForm.mobile)) {
                    self.$message.error('请输入有效手机号码');
                    return false;
                } else if (self.verifyCodeForm.mobile != this.initForm.bizerMobile) {
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
                if (this.verifyCodeForm.mobile != this.initForm.bizerMobile) {
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
                            this.$message.success('提现密码设置成功');
                            this.showSetPasswordDialog = false;
                        })
                    }
                })
            },
            canWithdraw() {
                let day = (new Date()).getDate();
                if (day == 10 || day == 20 || day == 30) {
                    this.noWithdraw = false;
                } else {
                    this.noWithdraw = true;
                }
            },
        },
        created() {
            this.init();
            this.canWithdraw();
        }
    }
</script>

<style scoped>

</style>