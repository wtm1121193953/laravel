<template>
    <el-container class="container">
        <el-header class="header">
            <!-- logo -->
            <div class="panel-logo fl" :style="{'background-color': theme.color}">
                <template v-if="logo_type === 1">
                    <img src="../../assets/images/logo.png" class="logo">
                </template>
                <template v-else>
                    <div class="logo p-l-20 p-r-20" :style="{'color': theme.menuTextColor}">{{title}}</div>
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
        </el-header>
        <el-container>
            <leftMenu :collapse="collapseLeftMenu" style="height: 100%" :class="{'uncollapse-menu': !collapseLeftMenu}" :menus="menus" ref="leftMenu"/>

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
            return {
                collapseLeftMenu: false,
                img: '',
                title: '',
                logo_type: null,

                showThemeSetting: false,
                themeSettingForm: {
                    name: '深蓝',
                    color: '',
                    menuTextColor: '',
                    menuActiveTextColor: '',
                }
            }
        },
        computed: {
            ...mapState([
                'theme',
                'globalLoading',
                'user',
                'menus'
            ]),
            username(){
                return this.user ? this.user.username : '';
            }
        },
        methods: {
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
                    api.post('/logout').then((res) => {
                        api.handlerRes(res).then(() => {
                            this.$message.success('退出成功')
                        })
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
                        api.get('/self/rules').then(res => {
                            api.handlerRes(res).then(data => {
                                store.dispatch('storeUserInfo', data);
                                store.dispatch('closeGlobalLoading')
                            })
                        });

                }
            },
            getTitleAndLogo() {
                document.title = '中交出行运营平台 - BOSS'
                this.logo_type = 2
                this.title = '中交出行运营平台 - BOSS'
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
    .uncollapse-menu {
        width: 200px;
    }
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