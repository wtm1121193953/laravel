<style lang="less" scoped>
    .login-container {
        width: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        background-color: #141a48;
        background-image: url(../../assets/images/login-bg.png);
        background-repeat: no-repeat;
        background-size: cover;
        overflow: hidden;
    }
    #loginThree {
        position: absolute;
        width: 100%;
        top: 0;
        bottom: 0;
        overflow: hidden;
    }
    .login-form {
        position: absolute;
        top: 50%;
        left: 50%;
        margin: -230px 0 0 -180px;
        width: 310px;
        padding: 25px;
        box-shadow: 0 0 100px rgba(0,0,0,.08);
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
            </div>
        </transition>
        <div id="loginThree"></div>

        <el-dialog title="忘记密码" :visible.sync="dialogForgetPassword" width="434px">
            <el-form :model="dialogForgetPasswordForm" ref="dialogForgetPasswordForm" :rules="dialogForgetPasswordFormRules" label-width="70px">
                <el-form-item label="帐号" prop="account">
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

        <el-dialog title="设置密码" :visible.sync="dialogSetPassword" width="454px">
            <el-form :model="dialogSetPasswordForm" ref="dialogSetPasswordForm" :rules="dialogSetPasswordFormRules" label-width="108px">
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
    import api from '../../assets/js/api'
    import THREE from '../../assets/js/three/three';
    import {mapState} from 'vuex'
    export default {
        data(){
            var validatePass = (rule, value, callback) => {        
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                  } else if (value !== this.dialogSetPasswordForm.password) {
                    callback(new Error('两次输入密码不一致!'));
                  } else {
                    callback();
                  }
            };
            var validateMobile = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入手机号'));
                    this.forgetPasswordValidation = false;
                  } else if (!/^1[3|4|5|7|8][0-9]\d{8}$/.test(value)) {
                    callback(new Error('手机号格式错误'));
                    this.forgetPasswordValidation = false;
                  } else {
                    callback();
                    this.forgetPasswordValidation = true;
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
                        { min: 4, max: 6, message: '请输入4位验证码', trigger: 'blur' }
                    ]
                },
                dialogSetPasswordForm: {
                    password: '',
                    confirm_password: ''
                },
                dialogSetPasswordFormRules: {
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        { min: 6, max: 12, message: '请输入6-12位密码', trigger: 'blur' }
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
                forgetPasswordValidation: false, //忘记密码框验证是否通过
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
            forgetPassword(){
                let _self = this;
                _self.$refs.dialogForgetPasswordForm.validate(valid => {
                    if(valid){
                        _self.forgetPasswordLoading = true;
                       api.post('/api/bizer/forgot_password', _self.dialogForgetPasswordForm).then(data => {
                           _self.dialogForgetPassword = false;
                            _self.dialogSetPassword = true;
                        }).catch((error) => {
                            console.log(error)
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
                    console.log(valid)
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
                if (!_self.forgetPasswordValidation) {
                    console.log(this.$refs.dialogForgetPasswordForm)
                    _self.$refs.dialogForgetPasswordForm.validateField('account')
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
                api.get('/sms/getVerifyCode', {'mobile':_self.dialogForgetPasswordForm.account}).then(data => {
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

            init3D () { // 初始化3D动画
                var SCREEN_WIDTH = window.innerWidth;
                var SCREEN_HEIGHT = window.innerHeight;
                var SEPARATION = 90;
                var AMOUNTX = 50;
                var AMOUNTY = 50;
                var container;
                var particles, particle;
                var count;
                var camera;
                var scene;
                var renderer;
                var mouseX = 0;
                var mouseY = 0;
                var windowHalfX = window.innerWidth / 2;
                var windowHalfY = window.innerHeight / 2;
                init();
                this.interval = setInterval(loop, 1000 / 60);
                function init() {
                    container = document.createElement( 'div' );
                    container.style.position = 'relative';
                    container.style.top = '200px';
                    document.getElementById('loginThree').appendChild( container );
                    camera = new THREE.Camera( 75, SCREEN_WIDTH / SCREEN_HEIGHT, 1, 10000 );
                    camera.position.z = 1000;
                    scene = new THREE.Scene();
                    renderer = new THREE.CanvasRenderer();
                    renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
                    particles = new Array();
                    var i = 0;
                    var material = new THREE.ParticleCircleMaterial( 0x097bdb, 1 );
                    for ( var ix = 0; ix < AMOUNTX; ix ++ ) {
                        for ( var iy = 0; iy < AMOUNTY; iy ++ ) {
                            particle = particles[ i ++ ] = new THREE.Particle( material );
                            particle.position.x = ix * SEPARATION - ( ( AMOUNTX * SEPARATION ) / 2 );
                            particle.position.z = iy * SEPARATION - ( ( AMOUNTY * SEPARATION ) / 2 );
                            scene.add( particle );
                        }
                    }
                    count = 0;
                    container.appendChild( renderer.domElement );
                    document.addEventListener( 'mousemove', onDocumentMouseMove, false );
                    document.addEventListener( 'touchmove', onDocumentTouchMove, false );
                }
                function onDocumentMouseMove( event ) {
                    mouseX = event.clientX - windowHalfX;
                    mouseY = event.clientY - windowHalfY;
                }
                function onDocumentTouchMove( event ) {
                    if ( event.touches.length == 1 ) {
                        event.preventDefault();
                        mouseX = event.touches[ 0 ].pageX - windowHalfX;
                        mouseY = event.touches[ 0 ].pageY - windowHalfY;
                    }
                }
                function loop() {
                    camera.position.x += ( mouseX - camera.position.x ) * .05;
                    camera.position.y = 364;
                    var i = 0;
                    for ( var ix = 0; ix < AMOUNTX; ix ++ ) {
                        for (var iy = 0; iy < AMOUNTY; iy++) {
                            particle = particles[ i++ ];
                            particle.position.y = ( Math.sin( ( ix + count ) * 0.3 ) * 50 ) + ( Math.sin( ( iy + count ) * 0.5 ) * 50 );
                            particle.scale.x = particle.scale.y = ( Math.sin( ( ix + count ) * 0.3 ) + 1 ) * 2 + ( Math.sin( ( iy + count ) * 0.5 ) + 1 ) * 2;
                        }
                    }
                    renderer.render(scene, camera);
                    count += 0.1;
                }
            }
        },
        created: function () {
        },
        mounted () {
            const that = this;
            that.showLogin = true;
            that.init3D();
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