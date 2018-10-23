<template>
    <div class="login-form form-label-noBefore" v-show="showLogin" v-loading="autoLoginLoading" element-loading-text="自动登录中...">
        <div class="login-link">
            <el-button type="text" @click="goLogin">已有帐号，立即登录</el-button>
        </div>
        <div class="login-logo">
            <span>{{projectName}} - {{systemName}}</span>
        </div>
        <el-form :model="form" :rules="formRules" ref="form"
                 @keyup.native.enter="doLogin"
                 label-position="right"
                 label-width="98px">
            <el-form-item label="帐号" prop="mobile" ref="mobile">
                <el-input type="text" v-model="form.mobile" auto-complete="off" placeholder="请输入手机号"/>
            </el-form-item>
            <el-form-item label="验证码" prop="verify_code">
                <el-input type="text" v-model="form.verify_code" auto-complete="off" class="w-150"
                          placeholder="请输入验证码" maxlength="4"/>
                <el-button type="primary" class="fr" style="width:132px;" :disabled="buttonCode.isDisabled" @click.native.prevent="sendCode">{{buttonCode.buttonName}}</el-button>
            </el-form-item>
            <el-form-item prop="name" label="昵称">
                <el-input v-model="form.name" maxlength="10" placeholder="请输入昵称"/>
            </el-form-item>
            <el-form-item label="设置密码" prop="password">
                <el-input type="password" v-model="form.password" auto-complete="off" placeholder="请设置6-12位密码，不区分大小写"/>
            </el-form-item>
            <el-form-item label="再次输入密码" prop="confirmPassword">
                <el-input type="password" v-model="form.confirmPassword" auto-complete="off" placeholder="请再次输入密码"/>
            </el-form-item>
            <el-form-item label-width="0">
                <el-button type="primary" style="width:75%; display: block; margin: 0 auto;" v-loading="loading" :disabled="loading"
                           @click.native.prevent="doLogin">立即注册
                </el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import {mapState} from 'vuex'

    export default {
        name: "register-form",
        data() {
            var validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (value !== this.form.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            var validateMobile = (rule, value, callback) => {
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
                    mobile: '',
                    name: '',
                    password: '',
                    confirmPassword:'',
                    verify_code: ''
                },
                formRules: {
                    mobile: [
                        {validator: validateMobile, trigger: 'blur'}
                    ],
                    name: [
                        {required: true, message: '昵称不能为空', trigger: 'blur'},
                    ],
                    verify_code: [
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                        {min: 4, max: 6, message: '请输入4位验证码', trigger: 'blur'}
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        {min: 6, max: 12, message: '请输入6-12位密码', trigger: 'blur'}
                    ],
                    confirmPassword: [
                        {validator: validatePass, trigger: 'blur'}
                    ]
                },
                loading: false,
                autoLoginLoading: false,
                showLogin: false,
                buttonCode:{
                    buttonName: "获取验证码",
                    isDisabled: false,
                    time: 60,
                },
                mobileValidate: false,//处理手机验证是否通过
            }
        },
        computed:{
            ...mapState([
                'projectName',
                'systemName',
                'user',
                'currentMenu'
            ])
        },
        methods: {
            goLogin() {
                router.push('/login');
            },
            relocation() {
                if (this.$route.query && this.$route.query._from) {
                    router.push(this.$route.query._from);
                }else if(this.currentMenu){
                    router.push('/');
                }else {
                    router.push('welcome');
                }
            },
            doLogin(){
                let _self = this;
                this.$refs.form.validate(valid => {
                    if(valid){
                        _self.loading = true;
                        api.post('/register', this.form).then(data => {
                            store.dispatch('storeUserInfo', data);
                            _self.relocation();
                        }).catch(() => {
                            // _self.refreshVerify();
                        }).finally(() => {
                            _self.loading = false;
                        })
                    }
                })
            },

            sendCode() {
                let _self = this;
                if (!_self.mobileValidate) {
                    this.$refs.form.validateField('mobile')
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
                api.get('/sms/getVerifyCode', this.form).then(data => {
                    store.dispatch('storeUserInfo', data);
                    _self.relocation();
                }).catch(() => {
                    // _self.refreshVerify();
                }).finally(() => {
                    _self.loading = false;
                })
            }
        },
        created: function () {
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
        margin: -230px 0 0 -225px;
        width: 400px;
        padding: 25px;
        box-shadow: 0 0 100px rgba(0,0,0,.08);
        background-color: #fff;
        border-radius: 4px;
        z-index: 3;
        .login-link {
            text-align: right;
            color: #eee;
            margin: -10px 0 10px;
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
    }
</style>
<style>
    .form-label-noBefore .el-form-item.is-required .el-form-item__label::before {
        content: none;
    }
</style>