<template>
    <el-menu
            :background-color="theme.color"
            :text-color="theme.menuTextColor"
            :active-text-color="theme.menuActiveTextColor"
            :default-active="currentMenu"
            :collapse="collapse"
            @select="change"
            :unique-opened="true"
            class="menu"
    >
        <template v-for="secMenu in menus">
            <el-submenu v-if="secMenu.sub && secMenu.sub.length > 0" :index="secMenu.url">
                <template slot="title">
                    <i>{{collapse ? secMenu.name.substr(0, 2) : ''}}</i><span slot="title">{{secMenu.name}}</span>
                </template>
                <el-menu-item v-for="(item,index) in secMenu.sub" :key="index" :index="item.url">{{item.name}}</el-menu-item>
            </el-submenu>
            <el-menu-item v-if="!secMenu.sub || secMenu.sub.length <= 0" :index="secMenu.url">
                <i>{{collapse ? secMenu.name.substr(0, 2) : ''}}</i><span slot="title">{{secMenu.name}}</span>
            </el-menu-item>
        </template>

    </el-menu>
</template>

<script>
    import {mapState} from 'vuex'

    export default {
        props: ['menus', 'collapse'],
        data() {
            return {
            }
        },
        computed: {
            ...mapState([
                'theme',
                'currentMenu',
            ])
        },
        methods: {
            change(key, query){
                store.commit('setCurrentMenu', key);
                if (key !== this.$route.path) {
                    router.push({path: key, query: query})
                } else {
                    router.replace({path: '/refresh', query: {name: this.$route.name, query: query}})
                }
            },
            reload(menus){
                this.menus = menus;
            }
        },
        created: function () {

            // 全局注册一个菜单对象
            Vue.prototype.$menu = this;

            Vue.nextTick(() => {
                if(this.$route.name === undefined && this.currentMenu){
                    router.push(this.currentMenu)
                }
            })
        }
    }
</script>
<style scoped>
    .menu:not(.el-menu--collapse){
        width: 200px;
        min-height: 400px;
    }
</style>