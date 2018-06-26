<template>
    <el-dialog :title="title" :visible.sync="isShow" width="60%">
        <div style="vertical-align: middle;text-align: center;">
            <el-carousel v-if="multi" height="700px">
                <el-carousel-item v-for="(item,index) in url" :key="index">
                    <img :src="item" alt="" style="max-height: 100%; max-width: 100%;">
                </el-carousel-item>
            </el-carousel>
            <img v-else style="max-width: 100%;max-height: 100%;" :src="url">
        </div>
    </el-dialog>
</template>

<script>
    export default {
        name: "img-preview-dialog",
        props: {
            visible: {type: Boolean, default: false},
            url: {type: String|Array, required: true},
            title: {type: String, default: '图片预览'}
        },
        computed: {
            multi(){
                return (typeof this.url != 'string')
            },
        },
        data() {
            return {
                isShow: false,
            }
        },
        watch: {
            isShow(newValue) {
                this.$emit('update:visible', newValue)
            },
            visible(val){
                this.isShow = val;
            }
        },
    }
</script>

<style scoped>

</style>