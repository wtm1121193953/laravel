<template>
    <el-row class="panel m-w-1280">
        <el-row class="panel-top">
            <div class="w-180 panel-logo">
                <template v-if="logo_type == '1'">
                    <img src="" class="logo">
                </template>
                <template v-else>
                    <span class="p-l-20 ">{{title}}</span>
                </template>
            </div>
            <div class="panel-top-menu">
                <el-col :span="1">
                    <div class="menu-switch" @click="showLeftMenu = !showLeftMenu"><i class="fa fa-bars"></i></div>
                </el-col>

                <el-col :span="23">
                    <el-menu theme="dark" mode="horizontal" @select="selectTopMenu">
                        <el-submenu index="options" style="float: right; right: 40px;">
                            <template slot="title">{{username}} <i class="fa fa-user" aria-hidden="true"></i></template>
                            <el-menu-item index="logout">退出</el-menu-item>
                        </el-submenu>
                    </el-menu>
                </el-col>
            </div>

        </el-row>

        <el-col :span="24" class="panel-center">
            <aside class="w-180" v-show="showLeftMenu">
                <leftMenu :menuList="menus" ref="leftMenu"></leftMenu>
            </aside>
            <section class="panel-c-c" :class="{'hide-leftMenu': !showLeftMenu}">
                <div class="grid-content bg-purple-light">
                    <el-col :span="24">
                        <transition name="fade" mode="out-in" appear>
                            <router-view v-loading="showLoading"></router-view>
                        </transition>
                    </el-col>
                </div>
            </section>
        </el-col>
    </el-row>
</template>
<style>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity .2s
    }
    .fade-enter,
    .fade-leave-active {
        opacity: 0
    }

    .panel {
        background: #324057;
        position: absolute;
        top: 0px;
        bottom: 0px;
        width: 100%;
    }
    .panel-top {
        height: 60px;
        line-height: 60px;
        color: #c0ccda;
        display: flex;
    }
    .panel-top-menu {
        flex: 1;
    }

    .panel-center {
        /*background: #324057;*/
        position: absolute;
        top: 60px;
        bottom: 0px;
        overflow: hidden;
    }

    .panel-c-c {
        /*background: #f1f2f7;*/
        background: #ffffff;
        position: absolute;
        right: 0px;
        top: 0px;
        bottom: 0px;
        left: 180px;
        overflow-x: hidden;
        padding: 20px;

        transition: left .5s;
        -webkit-transition: left .5s;
        -moz-transition: left .5s;
        -o-transition: left .5s;
    }

    .hide-leftMenu {
        left: 0;
        transition: left 0.5s;
        -webkit-transition: left .5s;
        -moz-transition: left .5s;
        -o-transition: left .5s;
    }

    .panel-logo {
        /*background-color: #324057;*/
        display: inline-block;
    }

    .logo {
        width: 150px;
        float: left;
        margin: 10px 10px 10px 18px;
    }

    .logout {
        background: url(../../../images/logout_36.png);
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

    .menu-switch {
        cursor: pointer;
    }
</style>
<script>
    import LeftMenu from '../../../js/components/left-menu.vue'

    export default {
        data() {
            return {
                showLeftMenu: true,
                showLoading: false,
                username: '',
                menus: [],
                hasChildMenu: false,
                img: '',
                title: '',
                logo_type: null
            }
        },
        methods: {
            logout() {
                this.$confirm('确认退出吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消'
                }).then(() => {
                    let data = {
                        userInfo : Lockr.get('userInfo')
                    }

                    Lockr.rm('menus')
                    Lockr.rm('userInfo')
                    console.log('logging-out!')
                    bus.$emit('user-logged-out')  // @see Login.vue
                    router.replace('/boss/login')
                }).catch(() => {

                })
            },
            switchTopMenu(item) {
                if (!item.child) {
                    router.push(item.url)
                } else {
                    router.push(item.child[0].child[0].url)
                }
            },
            selectTopMenu(key, keyPath){
                switch (key) {
                    case 'logout':
                        this.logout()
                        break
                }
            },
            getTitleAndLogo() {
                document.title = '项目管理 - BOSS'
                this.logo_type = '2'
                this.title = '项目管理 - BOSS'
            }
        },
        created() {
            if (this.$route.query && this.$route.query.__from) {
                router.push(this.$route.query.__from);
            }
            let userInfo = Lockr.get('userInfo');
            if(!userInfo){
                this.$message.warning('您尚未登录')
                router.replace('/boss/login')
                return ;
            }
            this.username = userInfo.name;
            this.menus = Lockr.get('menus');
            this.getTitleAndLogo()
        },
        components: {
            LeftMenu
        },
        watch: {
        },
//        mixins: [http]
    }
</script>