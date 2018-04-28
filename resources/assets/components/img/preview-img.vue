<template>

    <!--图片预览-->
    <div class="show-image-box">
        <img class="img" :src="url" width="100%" :width="width" :height="height" :alt="alt" @click="isShow=true">
        <el-dialog :title="title" :visible.sync="isShow">
            <div style="vertical-align: middle;text-align: center;">
                <el-carousel v-if="multi">
                    <el-carousel-item v-for="item in url" :key="item">
                        <img :src="item" alt="">
                    </el-carousel-item>
                </el-carousel>
                <img v-else style="max-width: 100%;max-height: 100%;" :src="url">
            </div>
        </el-dialog>
    </div>
</template>
<script>
    export default {
        props: {
            title: {type: String, default: '图片预览'},
            url: {type: String|Array, required: true},
            alt: {type: String, default: ''},
            width: {type: String, default: '100%'},
            height: {type: String, default: '100%'},
        },
        computed: {
            multi(){
                return (typeof this.url != 'string')
            }
        },
        data: function(){
            return {
                isShow: false
            }
        },
        mounted: function(){
            if(this.debug){
                console.log(this.url, typeof this.url)
            }
        }
    };
</script>

<style>

</style>
<style scoped>
    .img {
        cursor: pointer;
    }
</style>