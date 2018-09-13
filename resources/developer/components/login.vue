<style lang="less" scoped>
    .login-container {
        width: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        background-color: #141a48;
        background-image: url(../images/login-bg.jpg);
        background-repeat: no-repeat;
        background-size: cover;
        overflow: hidden;
    }
    .login-form {
        position: absolute;
        top: 50%;
        left: 50%;
        margin: -230px 0 0 -180px;
        width: 310px;
        height: 330px;
        padding: 25px;
        box-shadow: 0 0 100px rgba(0,0,0,.08);
        background-color: #fff;
        border-radius: 4px;
        z-index: 3;
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

        .verify-img {
            right: 0;
            height: 34px;
            margin: 1px;
            position: absolute;
        }
    }
    .form-fade-enter-active, .form-fade-leave-active {
        transition: all 1s;
    }
    .form-fade-enter, .form-fade-leave-active {
        transform: translate3d(0, -50px, 0);
        opacity: 0;
    }
</style>
<template>
    <div class="login-container">
        <transition name="form-fade" mode="in-out">
            <div class="login-form" v-show="showLogin" v-loading="autoLoginLoading" element-loading-text="自动登录中...">
                <div class="login-logo">
                    <span>{{projectName}} - {{systemName}}</span>
                </div>
                <el-form :model="form" :rules="formRules" ref="form"
                         @keyup.native.enter="doLogin"
                         label-position="left"
                         label-width="0px">
                    <el-form-item prop="username">
                        <el-input type="text" v-model="form.username" auto-complete="off" placeholder="帐号"/>
                    </el-form-item>
                    <el-form-item prop="password">
                        <el-input type="password" v-model="form.password" auto-complete="off" placeholder="密码"/>
                    </el-form-item>
                    <el-form-item prop="verifyCode">
                        <el-input type="text" v-model="form.verifyCode" auto-complete="off" class="w-150"
                                  placeholder="验证码"/>
                        <img class="verify-img" :src="captchaSrc" @click="refreshVerify()" width="150"/>
                        <div>
                            <el-checkbox v-model="rememberUsername">记住帐号</el-checkbox>
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" style="width:100%;" v-loading="loading" :disabled="loading"
                                   @click.native.prevent="doLogin">登录
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </transition>
    </div>
</template>

<script>
    import api from '../../assets/js/api'
    import THREE from '../../assets/js/three/three';
    import {mapState} from 'vuex'
    export default {
        data(){
            return {
                form: {
                    username: '',
                    password: '',
                    verifyCode: ''
                },
                formRules: {
                    username: [
                        {required: true, message: '请输入帐号', trigger: 'blur'}
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'}
                    ],
                    verifyCode: [
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                        { min: 4, max: 6, message: '请输入4-6位验证码', trigger: 'blur' }
                    ]
                },
                captchaUrl: captcha_url,
                captchaSrc: captcha_url + '?v=' + Math.random(),
                loading: false,
                autoLoginLoading: false,
                showLogin: false,
                rememberUsername: true,
            }
        },
        computed:{
            ...mapState([
                'projectName',
                'systemName',
                'user',
                'currentMenu',
                'loginUsername'
            ])
        },
        methods: {
            refreshVerify(){
                this.captchaSrc = ''
                setTimeout(() => {
                    this.captchaSrc = this.captchaUrl + '?v=' + moment().unix()
                }, 300)
            },
            relocation() {
                console.log('this.currentMenu', this.currentMenu)
                if (this.$route.query && this.$route.query._from) {
                    router.push(this.$route.query._from);
                }else {
                    router.push('/welcome');
                }
            },
            doLogin(){
                let _self = this;
                this.$refs.form.validate(valid => {
                    if(valid){
                        _self.loading = true;
                        api.post('/login', this.form).then(data => {
                            store.dispatch('storeUserInfo', data);
                            store.dispatch('setLoginUserName', this.rememberUsername ? this.form.username : '');
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
        created: function () {
            this.form.username = this.loginUsername;
        },
        mounted () {
            const that = this;
            that.showLogin = true;
        },
        beforeDestroy () {
            const self = this
            if (self.interval) clearInterval(self.interval);
        }
    }
</script>

<style scoped>
    .verify-pos {
        position: absolute;
        right: 100px;
        top: 0;
    }

    .card-box {
        padding: 20px;
        /*box-shadow: 0 0px 8px 0 rgba(0, 0, 0, 0.06), 0 1px 0px 0 rgba(0, 0, 0, 0.02);*/
        -webkit-border-radius: 5px;
        border-radius: 5px;
        -moz-border-radius: 5px;
        background-clip: padding-box;
        background-color: #F9FAFC;
        margin: 120px auto;
        width: 400px;
        border: 2px solid #8492A6;
    }

    .title {
        margin: 0 auto 40px auto;
        text-align: center;
        color: #505458;
    }

    .loginform {
        width: 350px;
        padding: 35px 35px 15px 35px;
    }
</style>