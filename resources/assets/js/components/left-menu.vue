<template>

    <div>
        <el-menu theme="dark" :default-active="currentMenu" @select="change" :unique-opened="true">
            <template v-for="menu in menuList">
                <el-menu-item v-if="!menu.sub || menu.sub.length <= 0" :index="menu.url">
                    <i v-if="menu.icon" :class="menu.icon"></i>
                    {{menu.name}}
                </el-menu-item>
                <el-submenu v-if="menu.sub && menu.sub.length > 0" :index="menu.url">
                    <template slot="title">
                        <i v-if="menu.icon" :class="menu.icon"></i>{{menu.name}}
                    </template>
                    <el-menu-item v-for="subMenu in menu.sub" :key="subMenu.url" :index="subMenu.url">
                        <i v-if="subMenu.icon" :class="subMenu.icon"></i>
                        {{subMenu.name}}
                    </el-menu-item>
                </el-submenu>
            </template>
        </el-menu>
    </div>
</template>

<script>
    export default {
        props: ['menuList'],
        data() {
            return {
                currentMenu: ''
            }
        },
        computed: {
//            currentMenu: function(){
//                return Lockr.get('current-menu')
//            }
        },
        methods: {
            change(key){
                this.currentMenu = key;
                if (key != this.$route.path) {
                    router.push(key)
                } else {
                    this.$refresh(this.$route.name)
                }
            },
            reload(menus){
                this.menuList = menus;
            }
        },
        created: function () {
            this.currentMenu = Lockr.get('current-menu') || '/boss/index';
            let _self  = this;

            // 全局注册一个菜单对象
            Vue.prototype.$menu = {
                change(key){ // 改变菜单
                    _self.change(key);
                },
                reload(menus){ // 重新加载菜单
                    _self.reload(menus)
                }
            };

            window.onbeforeunload = function(){
                Lockr.set('current-menu', _self.currentMenu);
            }
        }
    }
</script>