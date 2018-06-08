<template>
    <el-container class="container">
        <router-view v-loading="globalLoading"/>
    </el-container>
</template>
<script>
    import api from '../../assets/js/api'
    import {mapState, mapMutations, mapActions} from 'vuex'

    export default {
        data() {
            let validateReNewPassword = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入新密码'));
                } else if (value !== this.modifyPasswordForm.newPassword) {
                    callback(new Error('两次输入新密码不一致!'));
                } else {
                    callback();
                }
            };
            return {
                collapseLeftMenu: false,
                logo: '',
                logo_type: null,

                showThemeSetting: false,
                themeSettingForm: {
                    name: '深蓝',
                    color: '',
                    menuTextColor: '',
                    menuActiveTextColor: '',
                },

                showModifyPasswordForm: false,
                modifyPasswordForm: {
                    password: '',
                    newPassword: '',
                    reNewPassword: '',
                },
                modifyPasswordFormRules: {
                    password: [
                        {required: true, type: 'string', message: '请输入原密码'}
                    ],
                    newPassword: [
                        {required: true, type: 'string', message: '请输入新密码'},
                        { min: 6, max: 30, message: '密码必须在6-30位之间'}
                    ],
                    reNewPassword: [
                        { validator: validateReNewPassword }
                    ]
                }
            }
        },
        computed: {
            ...mapState([
                'projectName',
                'systemName',
                'theme',
                'globalLoading',
                'user',
                'menus',
            ]),
            username(){
                return this.user ? (this.user.merchantName || this.user.account) : '';
            }
        },
        methods: {
            getTitleAndLogo() {
                document.title = this.projectName + ' - ' + this.systemName
                this.logo_type = 2
                this.logo = this.systemName
            },
            ...mapMutations([
                'setTheme',
            ]),
            ...mapActions([
                'resetTheme',
                'setThemeByName'
            ]),
            resetTheme(){
                store.dispatch('resetTheme');
                this.themeSettingForm = deepCopy(store.state.theme);
            },
            setCustomTheme(){
                this.setTheme(this.themeSettingForm)
            },
            logout() {
                this.$confirm('确认退出吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消'
                }).then(() => {
                    api.post('/logout').then(() => {
                        this.$message.success('退出成功')
                    })
                    store.dispatch('clearUserInfo');
                    router.replace('/login')
                }).catch(() => {

                })
            },
            selectTopMenu(key, ){
                switch (key) {
                    case 'logout':
                        this.logout()
                        break
                    case 'theme-setting':
                        this.showThemeSetting = true;
                        break;
                    case 'refresh-rules':
                        store.dispatch('openGlobalLoading');
                        api.get('/self/rules').then(data => {
                            store.dispatch('storeUserInfo', data);
                            store.dispatch('closeGlobalLoading')
                        });
                        break;
                    case 'modify-password':
                        this.showModifyPasswordForm = true;
                        break;

                }
            },
            modifyPassword(){
                api.post('/self/modifyPassword', this.modifyPasswordForm).then(data => {
                    this.$message.success('修改密码成功')
                    this.showModifyPasswordForm = false;
                    this.$refs.modifyPasswordForm.resetFields();
                })
            },
        },
        created() {
            this.getTitleAndLogo();
            if(!this.user){
                this.$message.warning('您尚未登录');
                router.replace('/login');
                return ;
            }
            this.themeSettingForm = deepCopy(store.state.theme);

            //跳转到订单列表页
            const path = this.$router.history.current.path;
            if(path == '/') {
                router.replace('/orders');
            }
        },
        components: {},
        watch: {}
    }
</script>
<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity .5s
    }

    .fade-enter,
    .fade-leave-active {
        opacity: 0
    }

    .container {
        height: 100%;
    }

    .header {
        padding: 0;
        border-bottom: solid 1px #e6e6e6;
    }
    .header-main {
        padding: 0;
        height: 60px;
        line-height: 60px;
        text-align: center;
    }
    .header .menu-collapse {
        width: 60px;
        cursor: pointer;
        margin-right: 20px;
    }
    .header .header-dropdown-menu {
        margin-right: 30px;
    }
    .panel-logo {
        cursor: pointer;
        position: relative;
        height: 60px;
        width: 200px
    }
    .logo {
        width: 150px;
        float: left;
        margin: 6px 15px;
        line-height: 48px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .logout {
        background: url(../../assets/images/logout_36.png);
        background-size: contain;
        width: 20px;
        height: 20px;
        float: left;
    }

    .tip-logout {
        float: right;
        margin-right: 20px;
        padding-top: 5px;
        cursor: pointer;
    }

    .admin {
        color: #c0ccda;
        text-align: center;
    }

    .hide-leftMenu {
        left: 0;
    }
</style>