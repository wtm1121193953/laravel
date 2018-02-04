<template>

    <div>
        <el-menu
                :background-color="theme.color"
                :text-color="theme.menuTextColor"
                :active-text-color="theme.menuActiveTextColor"
                :default-active="currentMenu"
                :collapse="collapse"
                @select="change" :unique-opened="true"
                style="height: 100%;"
        >
            <template v-for="secMenu in menus">
                <el-submenu v-if="secMenu.sub && secMenu.sub.length > 0" :index="secMenu.url">
                    <template slot="title">
                        <i>{{collapse ? secMenu.name.substr(0, 2) : ''}}</i><span slot="title">{{secMenu.name}}</span>
                    </template>
                    <el-menu-item v-for="item in secMenu.sub" :key="item.url" :index="item.url">{{item.name}}</el-menu-item>
                </el-submenu>
                <el-menu-item v-if="!secMenu.sub || secMenu.sub.length <= 0" :index="secMenu.url">
                    <i>{{collapse ? secMenu.name.substr(0, 2) : ''}}</i><span slot="title">{{secMenu.name}}</span>
                </el-menu-item>
            </template>

        </el-menu>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {
        props: ['menus', 'collapse'],
        data() {
            return {
                currentMenu: ''
            }
        },
        computed: {
            ...mapState([
                'theme'
            ])
        },
        methods: {
            change(key){
                this.currentMenu = key;
                if (key != this.$route.path) {
                    router.push(key)
                } else {
                    router.replace({path: '/refresh', query: {name: this.$route.name}})
                }
            },
            reload(menus){
                this.menus = menus;
            },
            getFirstMenu(){ // 获取用户的第一个有效权限作为默认首页
                let firstRoute = '/';
                if(this.menus[0]){
                    if(this.menus[0].sub && this.menus[0].sub[0]){
                        firstRoute = this.menus[0].sub[0].url
                    }else{
                        firstRoute = this.menus[0].url;
                    }
                }
                return firstRoute;
            },
            toFirstMenu(){ // 转到第一个菜单
                this.change(this.getFirstMenu())
            }
        },
        created: function () {
            this.currentMenu = Lockr.get('current-menu') || this.getFirstMenu();

            let _self = this;
            // 全局注册一个菜单对象
            Vue.prototype.$menu = this;

            window.onbeforeunload = function(){
                Lockr.set('current-menu', _self.currentMenu);
            }
        }
    }
</script>