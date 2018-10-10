<template>
    <div class="login-form" v-show="showLogin" v-loading="autoLoginLoading" element-loading-text="自动登录中...">
        <div class="login-link">
            <el-button type="text" @click="goReg">注册</el-button>
            |
            <el-button type="text" @click="dialogForgetPassword = true">忘记密码</el-button>
        </div>
        <div class="login-logo">
            <span>{{projectName}} - {{systemName}}</span>
        </div>
        <el-form :model="form" :rules="formRules" ref="form"
                 @keyup.native.enter="doLogin"
                 label-position="left"
                 label-width="0px">
            <el-form-item prop="account">
                <el-input type="text" v-model="form.account" auto-complete="off" placeholder="帐号"/>
            </el-form-item>
            <el-form-item prop="password">
                <el-input type="password" v-model="form.password" auto-complete="off" placeholder="密码"/>
            </el-form-item>
            <el-form-item prop="verifyCode">
                <el-input type="text" v-model="form.verifyCode" auto-complete="off" class="w-150"
                          placeholder="验证码"/>
                <img class="verify-img" :src="captchaSrc" @click="refreshVerify()" width="150"/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" style="width:100%;" v-loading="loading" :disabled="loading"
                           @click.native.prevent="doLogin">登录
                </el-button>
            </el-form-item>
        </el-form>

        <!-- 忘记密码弹框 -->
        <el-dialog title="忘记密码" :visible.sync="dialogForgetPassword" width="434px">
            <el-form class="form-label-noBefore" :model="dialogForgetPasswordForm" ref="dialogForgetPasswordForm" :rules="dialogForgetPasswordFormRules" label-width="70px">
                <el-form-item label="帐号" prop="mobile">
                    <el-input type="text" v-model="dialogForgetPasswordForm.mobile" auto-complete="off" placeholder="请输入手机号"/>
                </el-form-item>
                <el-form-item label="验证码" prop="verify_code">
                    <el-input type="text" v-model="dialogForgetPasswordForm.verify_code" auto-complete="off" placeholder="请输入验证码" class="w-180" maxlength="4"/>
                    <el-button type="primary" class="fr" style="width:132px;" :disabled="buttonCode.isDisabled" @click.native.prevent="sendCode">{{buttonCode.buttonName}}</el-button>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button  v-loading="forgetPasswordLoading" :disabled="forgetPasswordLoading" type="primary" @click="forgetPassword">提交</el-button>
            </div>
        </el-dialog>

        <!-- 重设密码弹框 -->
        <el-dialog title="设置密码" :visible.sync="dialogSetPassword" width="454px">
            <el-form class="form-label-noBefore" :model="dialogSetPasswordForm" ref="dialogSetPasswordForm" :rules="dialogSetPasswordFormRules" label-width="108px">
                <el-form-item label="设置密码" prop="password">
                    <el-input type="password" v-model="dialogSetPasswordForm.password" auto-complete="off" placeholder="请设置6-12位密码，不区分大小写"/>
                </el-form-item>
                <el-form-item label="再次输入密码" prop="confirm_password">
                    <el-input type="password" v-model="dialogSetPasswordForm.confirm_password" auto-complete="off" placeholder="请再次输入密码"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary"  v-loading="setPasswordLoading" :disabled="setPasswordLoading" @click="setPassword">提交</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import {mapState} from 'vuex'
    export default {
        name: "login-form",
        data() {
            let validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (value !== this.dialogSetPasswordForm.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            let validateMobile = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入手机号'));
                    this.mobileValidate = false;
                } else if (!/^1[3456789][0-9]\d{8}$/.test(value)) {
                    callback(new Error('手机号格式错误'));
                    this.mobileValidate = false;
                } else {
                    callback();
                    this.mobileValidate = true;
                }
            };
            return {
                form: {
                    account: '',
                    password: '',
                    verifyCode: ''
                },
                formRules: {
                    account: [
                        {validator: validateMobile, trigger: 'blur'}
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'}
                    ],
                    verifyCode: [
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                        {min: 4, max: 6, message: '请输入4-6位验证码', trigger: 'blur'}
                    ]
                },
                captchaUrl: captcha_url,
                captchaSrc: captcha_url + '?v=' + Math.random(),
                loading: false,
                autoLoginLoading: false,
                showLogin: false,
                forgetPasswordLoading: false,
                dialogForgetPassword: false,
                dialogSetPassword: false,
                dialogForgetPasswordForm: {
                    mobile: '',
                    verify_code: ''
                },
                dialogForgetPasswordFormRules: {
                    mobile:[
                        {validator: validateMobile, trigger: 'blur'}
                    ],
                    verify_code:[
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                        {min: 4, max: 6, message: '请输入4位验证码', trigger: 'blur'}
                    ]
                },
                dialogSetPasswordForm: {
                    password: '',
                    confirm_password: ''
                },
                dialogSetPasswordFormRules: {
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        {min: 6, max: 12, message: '请输入6-12位密码', trigger: 'blur'}
                    ],
                    confirm_password: [
                        {validator: validatePass, trigger: 'blur'}
                    ]
                },
                setPasswordLoading: false,
                buttonCode:{
                    buttonName: "获取验证码",
                    isDisabled: false,
                    time: 60,
                },
                mobileValidate: false, //处理手机验证是否通过
            }
        },
        computed: {
            ...mapState([
                'projectName',
                'systemName',
                'user',
                'currentMenu'
            ])
        },
        methods: {
            forgetPassword(){
                let _self = this;
                _self.$refs.dialogForgetPasswordForm.validate(valid => {
                    if(valid){
                        _self.forgetPasswordLoading = true;
                        api.post('/api/bizer/forgot_password', _self.dialogForgetPasswordForm).then(data => {
                            _self.dialogForgetPassword = false;
                            _self.dialogSetPassword = true;
                        }).catch((error) => {
                            // console.log(error)
                            _self.$message({
                                message: error.response && error.response.message ? error.response.message:'请求失败',
                                type: 'warning'
                            });
                        }).finally(() => {
                            _self.forgetPasswordLoading = false;
                        })
                    }
                })

            },
            setPassword(){
                let _self = this;
                _self.$refs.dialogSetPasswordForm.validate(valid => {
                    // console.log(valid)
                    if(valid){
                        let params = _self.dialogForgetPasswordForm;
                        Object.assign(params, this.dialogSetPasswordForm);
                        _self.setPasswordLoading = true;
                        api.post('/api/bizer/forgot_password', params).then(data => {
                            _self.dialogSetPassword = false;
                            _self.$message({
                                message: '设置密码成功',
                                type: 'success'
                            });
                        }).catch(() => {
                            _self.$message({
                                message: '设置密码失败',
                                type: 'warning'
                            });
                        }).finally(() => {
                            _self.setPasswordLoading = false;
                        })
                    }
                })

            },
            sendCode() {
                let _self = this;
                if (!_self.mobileValidate) {
                    // console.log(this.$refs.dialogForgetPasswordForm)
                    _self.$refs.dialogForgetPasswordForm.validateField('mobile')
                    return;
                }
                _self.buttonCode.isDisabled = true;
                let interval = window.setInterval(function() {
                    _self.buttonCode.buttonName = '重新发送(' +  _self.buttonCode.time + 's)';
                    --_self.buttonCode.time;
                    if(_self.buttonCode.time < 0) {
                        _self.buttonCode.buttonName = "获取验证码";
                        _self.buttonCode.time = 60;
                        _self.buttonCode.isDisabled = false;
                        window.clearInterval(interval);
                    }
                }, 1000);
                api.get('/sms/getVerifyCode', {'mobile':_self.dialogForgetPasswordForm.mobile}).then(data => {
                    _self.$message({
                        message: '发送验证码成功',
                        type: 'success'
                    });
                }).catch(() => {
                    _self.$message({
                        message: '发送验证码失败',
                        type: 'warning'
                    });
                    // _self.refreshVerify();
                }).finally(() => {
                    // _self.loading = false;
                })
            },
            goReg() {
                router.push('/register');
            },
            refreshVerify(){
                this.captchaSrc = ''
                setTimeout(() => {
                    this.captchaSrc = this.captchaUrl + '?v=' + moment().unix()
                }, 300)
            },
            relocation() {
                if (this.$route.query && this.$route.query._from) {
                    router.push(this.$route.query._from);
                }else if(this.currentMenu){
                    router.push(this.currentMenu);
                }else {
                    router.push('welcome');
                }
            },
            doLogin(){
                let _self = this;
                this.$refs.form.validate(valid => {
                    if(valid){
                        _self.loading = true;
                        api.post('/login', this.form).then(data => {
                            store.dispatch('storeUserInfo', data);
                            _self.relocation();
                        }).catch(() => {
                            _self.refreshVerify();
                        }).finally(() => {
                            _self.loading = false;
                        })
                    }
                })

            },

        },
        mounted () {
            const that = this;
            that.showLogin = true;
        },
    }
</script>

<style lang="less" scoped>

    .login-form {
        position: absolute;
        top: 50%;
        left: 50%;
        margin: -230px 0 0 -180px;
        width: 310px;
        padding: 25px;
        box-shadow: 0 0 100px rgba(0, 0, 0, .08);
        background-color: #fff;
        border-radius: 4px;
        z-index: 3;
        .login-link {
            text-align: right;
            color: #eee;
            margin: -10px 0 10px;
            .el-button {
                margin: 0 5px;
            }
        }
        .login-logo {
            text-align: center;
            height: 40px;
            line-height: 40px;
            cursor: pointer;
            margin-bottom: 24px;
            img {
                width: 40px;
                margin-right: 8px;
            }
            span {
                vertical-align: text-bottom;
                font-size: 16px;
                text-transform: uppercase;
                display: inline-block;
            }
        }
        .el-form-item:last-child {
            margin-bottom: 0;
        }
        .verify-img {
            right: 0;
            height: 38px;
            margin: 1px;
            position: absolute;
        }
    }
</style>
<style>
    .form-label-noBefore .el-form-item.is-required .el-form-item__label::before {
        content: none;
    }
</style>