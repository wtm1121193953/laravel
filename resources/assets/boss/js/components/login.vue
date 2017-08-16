<template>
    <el-row type="flex" justify="center" align="middle">
        <el-col class="login-box">
            <el-card header="项目管理系统">
                <el-form :model="form" ref="form" @keyup.native.enter="doLogin('form')" label-position="left" label-width="0px" >
                    <el-form-item prop="username">
                        <el-input type="text" v-model="form.username" auto-complete="off" placeholder="账号"></el-input>
                    </el-form-item>
                    <el-form-item prop="password">
                        <el-input type="password" v-model="form.password" auto-complete="off" placeholder="密码"></el-input>
                    </el-form-item>
                    <el-form-item prop="verifyCode">
                        <el-input type="text" v-model="form.verifyCode" auto-complete="off" placeholder="验证码"
                                  class="w-150"></el-input>
                        <img :src="verifyUrl" @click="refreshVerify()" width="120" style="right: 50px; cursor: pointer;" class="verify-pos"/>
                    </el-form-item>
                    <el-form-item style="width:100%;">
                        <el-button type="primary" style="width:100%;" v-loading="loading"
                                   @click.native.prevent="doLogin('form')">登录
                        </el-button>
                    </el-form-item>
                </el-form>
            </el-card>
        </el-col>
    </el-row>
</template>

<script>
//    import api from '../assets/js/api'
    export default {
        data(){
            return {
                form: {
                    username: '',
                    password: '',
                    verifyCode: ''
                },
                verifyUrl: captcha_src,
                loading: false
            }
        },
        methods: {
            refreshVerify(){
                let url = this.verifyUrl.substring(0, this.verifyUrl.indexOf('?'));
                this.verifyUrl = ''
                this.verifyUrl = url + '?v=' + Math.random()
            },
            relocation() {
                if (this.$route.query && this.$route.query.__from) {
                    router.replace(this.$route.query.__from);
                }else{
                    let lastVisitedMenu = Lockr.get('current-menu');
                    if(lastVisitedMenu){
                        router.replace(lastVisitedMenu);
                    } else{
                        Lockr.set('current-menu', this.getFirstRoute());
                        router.replace(this.getFirstRoute());
                    }
                }
            },
            getFirstRoute(){ // 获取用户的第一个有效权限作为默认首页
                let menus = Lockr.get('menus');
                let firstRoute = '/boss/index';
                menus.forEach((menu) => {
                    if (menu.sub  && menu.sub[0]  && menu.sub[0].url !== '' ) {
                        firstRoute = menu.sub[0].url;
                        return false;
                    }
                });
                return firstRoute;
            },
            doLogin(){
                let _self = this;
                this.loading = true;
                api.post('/api/boss/login', this.form).then((res) => {
                    _self.loading = false;
                    api.handlerRes(res).then(data => {
                        Lockr.set('menus', data.menus);
                        Lockr.set('userInfo', data.userInfo);
                        _self.relocation();
                    }).catch(() => {
                        _self.refreshVerify();
                    });
                }).catch(() => {
                    _self.refreshVerify();
                })
            }
        },
        mounted: function(){
            bus.$on('user-logged-out',this.refreshVerify);
        },
        created: function () {
            let _self = this;

            //如果用户在tsp那边更换了登陆用户，那么在tsp点击osp链接后，osp的用户也要更新
            if (_self.$route.query && _self.$route.query.sign) {
//                api.doLoginWithSign(_self.$route.query).then(data => {
//                    Lockr.set('userMenuList', data.menuList)
//                    Lockr.set('data', data)
//                    Lockr.set('userInfo', data.userInfo)
//                    _self.relocation();
//                });
            }else{
                let userInfo = Lockr.get('userInfo')
                if (userInfo) {
                    _self.relocation();
                }
            }

//            let userInfo = Lockr.get('userInfo')
//            if (userInfo) {
//                _self.relocation();
//            } else {
//                if (_self.$route.query && _self.$route.query.sign) {
//                    api.doLoginWithSign(_self.$route.query).then(data => {
//                        Lockr.set('userMenuList', data.menuList)
//                        Lockr.set('data', data)
//                        Lockr.set('userInfo', data.userInfo)
//                        _self.relocation()
//                    });
//                }
//            }
        }
    }

</script>

<style>
    .login-box {
        margin-top: 120px;
        width:400px;
    }
    .verify-pos {
        position: absolute;
        right: 100px;
        top: 0px;
    }

</style>