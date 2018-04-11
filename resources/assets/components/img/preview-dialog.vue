<template>
    <el-dialog :title="title" :visible.sync="isShow">
        <div style="vertical-align: middle;text-align: center;">
            <img style="max-width: 100%;max-height: 100%;" :src="url">
        </div>
    </el-dialog>
</template>

<script>
    export default {
        name: "img-preview-dialog",
        props: {
            visible: {type: Boolean, default: false},
            url: {type: String, required: true},
            title: {type: String, default: '图片预览'}
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