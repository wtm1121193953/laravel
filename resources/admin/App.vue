<template>
    <div id="app">
        <transition name="fade">
            <router-view  v-if="isRouterAlive" />
        </transition>
    </div>
</template>

<script>
    export default {
        name: 'app',
        provide(){
            return {
                reloads:this.reloads
            }
        },
        components: {},
        data(){
            return {
                isRouterAlive:true
            }
        },
        methods:{
            reloads(){
                this.isRouterAlive = false
                this.$nextTick(function () {
                    this.isRouterAlive = true
                })
            }

        }
    }
</script>

<style>

    .bounce-enter-active {
        animation: bounce-in .5s;
    }

    .bounce-leave-active {
        animation: bounce-out .2s;
    }

    @keyframes bounce-in {
        0% {
            transform: scale(0);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes bounce-out {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(0.95);
        }
        100% {
            transform: scale(0);
        }
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Helvetica Neue, Helvetica, PingFang SC, Hiragino Sans GB, Microsoft YaHei, SimSun, sans-serif;
        font-weight: 400;
        -webkit-font-smoothing: antialiased;
    }

    #app {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 100%;
    }

    .el-submenu [class^=fa] {
        vertical-align: baseline;
        margin-right: 10px;
    }

    .el-menu-item [class^=fa] {
        vertical-align: baseline;
        margin-right: 10px;
    }
</style>