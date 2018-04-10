<template>
    <el-container class="container">
        <el-header class="header">
            <!-- logo -->
            <div class="panel-logo fl" :style="{'background-color': theme.color}">
                <template v-if="logo_type === 1">
                    <img src="../../assets/images/logo.png" class="logo">
                </template>
                <template v-else>
                    <div class="logo p-l-20 p-r-20" :style="{'color': theme.menuTextColor}">{{logo}}</div>
                </template>
            </div>
            <div class="top-left-menu" style="height: 60px; float: left; line-height: 60px; text-align: center;" :style="{'background-color': theme.color, 'color': theme.menuTextColor}">
                <div class="fl" style="width: 60px; cursor: pointer;" @click="collapseLeftMenu = !collapseLeftMenu"><i class="el-icon-menu"></i></div>
                <!--<div class="fl" style="width: 75px"> V 2.0 </div>-->
            </div>
            <!-- 菜单前的下边框 -->
            <div class="mock-menu-bottom-line"></div>

            <!-- 顶部菜单 -->
            <el-menu
                    :background-color="theme.color"
                    :text-color="theme.menuTextColor"
                    :active-text-color="theme.menuActiveTextColor"
                    mode="horizontal"
                    @select="selectTopMenu"
                    style="margin-left: 260px"
            >
                <el-submenu index="options" :show-timeout="100" style="float: right;">
                    <template slot="title">{{username}} <i class="fa fa-user" aria-hidden="true"></i></template>
                    <el-menu-item index="refresh-rules" class="top-menu-item">刷新权限</el-menu-item>
                    <el-menu-item index="theme-setting" class="top-menu-item">主题设置</el-menu-item>
                    <el-menu-item index="modify-password" class="top-menu-item">修改密码</el-menu-item>
                    <el-menu-item index="logout" class="top-menu-item">退出</el-menu-item>
                </el-submenu>
            </el-menu>

            <!-- 主题设置面板 -->
            <el-dialog :visible.sync="showThemeSetting" title="主题设置">
                <el-form>
                    <el-form-item lable="选择主题">
                        <el-radio-group v-model="themeSettingForm.name" @change="setThemeByName">
                            <el-radio label="深蓝">深蓝</el-radio>
                            <el-radio label="深灰">深灰</el-radio>
                            <el-radio label="亮白">亮白</el-radio>
                            <el-radio label="custom">自定义</el-radio>
                        </el-radio-group>
                        <el-button type="text" class="fr" @click="resetTheme">重置主题</el-button>
                    </el-form-item>

                    <el-form-item v-if="themeSettingForm.name === 'custom'">
                        <el-form-item label="背景颜色">
                            <el-color-picker
                                    v-model="themeSettingForm.color"
                                    @change="setCustomTheme"
                            />
                        </el-form-item>
                        <el-form-item label="字体颜色">
                            <el-color-picker
                                    v-model="themeSettingForm.menuTextColor"
                                    @change="setCustomTheme"
                            />
                        </el-form-item>
                        <el-form-item label="高亮字体颜色">
                            <el-color-picker
                                    v-model="themeSettingForm.menuActiveTextColor"
                                    @change="setCustomTheme"
                            />
                        </el-form-item>
                    </el-form-item>
                </el-form>
            </el-dialog>

            <!-- 修改密码面板 -->
            <el-dialog :visible.sync="showModifyPasswordForm" title="修改密码">
                <el-form :model="modifyPasswordForm" label-width="100px" ref="modifyPasswordForm" :rules="modifyPasswordFormRules">
                    <el-form-item label="原密码" prop="password">
                        <el-input type="password" v-model="modifyPasswordForm.password" placeholder="请输入原密码"/>
                    </el-form-item>
                    <el-form-item label="新密码" prop="newPassword">
                        <el-input type="password" v-model="modifyPasswordForm.newPassword" placeholder="请输入新密码"/>
                    </el-form-item>
                    <el-form-item label="确认新密码" prop="reNewPassword">
                        <el-input type="password" v-model="modifyPasswordForm.reNewPassword" placeholder="请再次输入新密码"/>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="modifyPassword">确定</el-button>
                        <el-button @click="showModifyPasswordForm=false">取消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>
        </el-header>
        <el-container>
            <leftMenu :collapse="collapseLeftMenu" :menus="menus" ref="leftMenu"/>

            <el-main>
                <el-col :span="24">
                    <!--<transition name="fade" mode="out-in" appear>-->
                    <router-view v-loading="globalLoading"/>
                    <!-- </transition> -->
                </el-col>
            </el-main>
        </el-container>
    </el-container>
</template>
<script>
    import api from '../../assets/js/api'
    import leftMenu from '../../assets/components/leftMenu.vue'
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
                return this.user ? this.user.username : '';
            }
        },
        methods: {
            getTitleAndLogo() {
                document.title = this.projectName + ' - ' + this.systemName
                this.logo_type = 2
                this.logo = this.projectName
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
        },
        components: {
            leftMenu,
        },
        watch: {
        }
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
    }
    .mock-menu-bottom-line {
        position:absolute; height: 0; width: 260px; top: 60px; border-bottom: solid 1px #e6e6e6;
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
    .top-menu-item {
        min-width: 0;
        width: 100%
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