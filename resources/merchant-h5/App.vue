<style lang="less">
    //引入公共样式 - 移动端
    @import url(./assets/style/vectors.less);
</style>


<template>
    <div id="app">
        <transition name="fade">
            <router-view/>
        </transition>
    </div>
</template>

<script>
    export default {
        name: 'app',
        components: {},
        mounted() {
            //挂载之后添加meta标签，替换之前pc端的，适用于移动端避免缩放
            const meta = document.createElement('meta')
            meta.setAttribute("name", "viewport")
            meta.setAttribute("content", "width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no")
            document.getElementsByTagName('head')[0].appendChild(meta)
        }
    }
</script>

<style>
    @import url(./assets/style/ionicons/css/ionicons.css);

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
</style>