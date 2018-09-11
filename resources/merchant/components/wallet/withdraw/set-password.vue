<template>
    <page title="提现密码设置">
        <el-col :span="6">
            <el-button v-if="editPassword" @click="editPassword = false">修改提现密码</el-button>
            <el-form :model="form" status-icon :rules="formRules" ref="form" v-if="!editPassword" label-width="100px">
                <el-form-item label="商户手机号码">
                    {{mobile}}
                    <el-button type="success" @click="getVerifyCode" :disabled="isDisabled" size="small" style="margin-left: 10px">{{buttonName}}</el-button>
                </el-form-item>
                <el-form-item prop="verifyCode" label="短信验证码">
                    <el-input v-model="form.verifyCode" :maxlength="6" placeholder="短信验证码"/>
                </el-form-item>
                <el-form-item label="设置提现密码" prop="password">
                    <el-input type="password" v-model="form.password" :maxlength="6" auto-complete="off" placeholder="6位纯数字密码"></el-input>
                </el-form-item>
                <el-form-item label="再次确认密码" prop="checkPassword">
                    <el-input type="password" v-model="form.checkPassword" :maxlength="6" auto-complete="off" placeholder="再次输入提现密码"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取 消</el-button>
                    <el-button type="primary" @click="commit">确 定</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-withdraw-set-password",
        data() {
            let validateVerifyCode = (rule, value, callback) => {
                if (!value) {
                    return callback(new Error('验证码不能为空'));
                }
                setTimeout(() => {
                    if (isNaN(value) || value.indexOf(' ') >= 0) {
                        callback(new Error('请输入数字值'));
                    } else {
                        callback();
                    }
                }, 100);
            };
            let validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                } else if (value.length !== 6) {
                    callback(new Error('密码需为6位'));
                } else {
                    if (this.form.checkPassword !== '') {
                        this.$refs.form.validateField('checkPassword');
                    }
                    callback();
                }
            };
            let validateCheckPass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                } else if (value !== this.form.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };

            return {
                mobile: '',
                editPassword: false,
                form: {
                    verifyCode: '',
                    password: '',
                    checkPassword: '',
                },
                buttonName: '获取验证码',
                isDisabled: false,
                time: 60,

                formRules: {
                    verifyCode: [
                        {validator: validateVerifyCode}
                    ],
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
            commit() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        if (!(/^1[3,4,5,6,7,8,9]\d{9}$/.test(this.mobile))) {
                            this.$message.error('手机号码格式错误');
                            return false;
                        }
                        this.form.mobile = this.mobile;
                        api.post('/wallet/withdraw/setWalletPassword', this.form).then(data => {
                            this.$message.success('提现密码设置成功');
                            this.$refs.form.resetFields();
                            this.initForm();
                            if(this.$route.query.redirect){
                                router.replace(this.$route.query.redirect)
                            }
                        });
                    }
                })
            },
            cancel() {
                if(this.$route.query.redirect){
                    router.replace(this.$route.query.redirect)
                }
                this.editPassword = true;
            },
            getVerifyCode() {
                let self = this;

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
                api.get('sms/getVerifyCode', {mobile: self.mobile}).then(() => {
                    self.$message.success('发送成功');
                })
            },
            initForm() {
                api.get('/wallet/withdraw/getPasswordInfo').then(data => {
                    this.mobile = data.mobile;
                    this.editPassword = data.editPassword;
                })
            }
        },
        created() {
            this.initForm();
        }
    }
</script>

<style scoped>

</style>