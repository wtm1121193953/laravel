<template>
    <el-container class="container">
        <el-header class="header">
            <el-container>
                <!-- logo -->
                <el-aside width="200px">
                    <div class="panel-logo fl" :style="{'background-color': theme.color}">
                        <template v-if="logo_type === 1">
                            <img src="../../assets/images/logo.png" class="logo">
                        </template>
                        <template v-else>
                            <div class="logo p-l-20" :style="{'color': theme.menuTextColor}">{{logo}}</div>
                        </template>
                    </div>
                </el-aside>
                <el-main class="header-main" :style="{'background-color': theme.color, 'color': theme.menuTextColor}">
                    <div class="fl menu-collapse" @click="collapseLeftMenu = !collapseLeftMenu"><i class="el-icon-menu"></i></div>
                    <!--<div class="fl" >{{user.merchantName}}</div>-->

                    <!-- 顶部菜单 -->
                    <div class="fr header-dropdown-menu">
                        <el-dropdown trigger="click" @command="selectTopMenu">
                            <el-button type="text" :style="{color: theme.menuTextColor}">
                                {{ username }} <i class="el-icon-arrow-down el-icon--right"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <!--<el-dropdown-item command="refresh-rules">刷新权限</el-dropdown-item>-->
                                <!--<el-dropdown-item command="merchant-info">商户资料</el-dropdown-item>-->
                                <el-dropdown-item command="theme-setting">主题设置</el-dropdown-item>
                                <el-dropdown-item command="modify-password">修改密码</el-dropdown-item>
                                <el-dropdown-item command="logout">退出</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </div>
                </el-main>

            </el-container>

            <!-- 展示商户信息 -->
            <el-dialog :visible.sync="showMerchantInfo" title="商户资料">
                <el-form :model="showMerchantInfoForm" label-width="100px" ref="showMerchantInfoForm" :rules="showMerchantInfoFormRules">
                    <el-form-item label="商户ID:">{{showMerchantInfoForm.id}}</el-form-item>
                    <el-form-item label="商户名称:">{{showMerchantInfoForm.name}}</el-form-item>
                    <el-form-item label="商户招牌名称:">{{showMerchantInfoForm.signboard_name}}</el-form-item>
                    <el-form-item label="商户行业:">{{showMerchantInfoForm.categoryPathText}}</el-form-item>
                    <el-form-item label="商户省市区:">{{showMerchantInfoForm.province}}-{{showMerchantInfoForm.city}}-{{showMerchantInfoForm.area}}</el-form-item>
                    <el-form-item label="详细地址:">{{showMerchantInfoForm.address}}</el-form-item>
                    <el-form-item label="商户简介:">{{showMerchantInfoForm.desc}}</el-form-item>
                </el-form>
            </el-dialog>

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
                    <el-form-item label="确认新密码" prop="reNewPassword" required>
                        <el-input type="password" v-model="modifyPasswordForm.reNewPassword" placeholder="请再次输入新密码"/>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="modifyPassword">确定</el-button>
                        <el-button @click="showModifyPasswordForm=false">取消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>

            <el-dialog
                    title=""
                    :visible.sync="showElectronicContract"
                    center
                    :close-on-click-modal="false"
                    :close-on-press-escape="false"
                    :show-close="false"
            >
                <electronic-contract
                    @closeElectronicContract="showElectronicContract = false"
                ></electronic-contract>
            </el-dialog>

        </el-header>
        <el-container>
            <leftMenu :collapse="collapseLeftMenu" :menus="menus" ref="leftMenu"/>

            <el-main style="overflow-y: scroll;">
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
    import ElectronicContract from './electronic-contract/contract-detail'
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
                },
                showMerchantInfo:false,
                showMerchantInfoForm:{},
                showMerchantInfoFormRules:{},

                showElectronicContract: false,
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
                'electronicContract',
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
                    case 'merchant-info':
                        this.showMerchantInfo = true;
                        api.get('/self/getMerchantInfo').then(data => {
                            this.showMerchantInfoForm  = data;
                        });
                        break;
                }
            },
            modifyPassword(){
                this.$refs.modifyPasswordForm.validate(valid => {
                    if(valid){
                        api.post('/self/modifyPassword', this.modifyPasswordForm).then(data => {
                            this.$message.success('修改密码成功')
                            this.showModifyPasswordForm = false;
                            this.$refs.modifyPasswordForm.resetFields();
                        })
                    }
                })
            },

            getMenus(){
                api.get('/self/menus').then(data => {
                    if(data.user && data.user.type == 1){
                        location.href = '/merchant/'
                    }else {
                        store.dispatch('storeUserInfo', data);
                    }
                });
            },

            checkMerchantElectronicContract() {
                if (this.electronicContract && (this.electronicContract.contract == null || this.electronicContract.contract.status == 0) && this.electronicContract.contractSwitch == 1 && this.electronicContract.payToPlatform > 0) {
                    this.showElectronicContract = true;
                }
            }

        },
        created() {
            this.getTitleAndLogo();
            if(!this.user){
                this.$message.warning('您尚未登录');
                router.replace('/login');
                return ;
            }
            // this.checkMerchantElectronicContract();
            this.themeSettingForm = deepCopy(store.state.theme);

            // 刷新页面时重新获取一下权限
            this.getMenus()
        },
        components: {
            leftMenu,
            ElectronicContract,
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