<template>
    <el-upload
            class="uploader"
            :action="action"
            :show-file-list="false"
            :on-success="handleUploadSuccess"
            :before-upload="beforeAvatarUpload">
        <img v-if="value" :src="value" class="img" :style="{height: imageHeight}">
        <i v-else class="el-icon-plus uploader-icon" :style="{height: iconHeight, 'line-height': iconLineHeight}"></i>
    </el-upload>
</template>

<script>
    import emitter from 'element-ui/src/mixins/emitter';
    /**
     * 单图片上传组件
     * todo 选项: 是否可删除, 禁用,
     * todo 功能: 删除按钮
     * todo 事件: before, success, fail, complete
     */
    export default {
        name: "single-image-upload",
        props: {
            value: {type: String},
            action: {type: String, default: '/pub/upload/index'},
            width: {type: Number},
            height: {type: Number},
        },
        mixins: [emitter],
        computed: {
            imageHeight(){
                return this.height && this.width ? (this.height / this.width * 180) + 'px' : 'auto';
            },
            iconHeight(){
                return this.height && this.width ? (this.height / this.width * 180) + 'px' : '180px';
            },
            iconLineHeight(){
                let lineHeight = this.height && this.width ? (this.height / this.width * 180 - 2) : 178;
                if(lineHeight < 28){
                    lineHeight = 28;
                }
                return lineHeight + 'px';
            }
        },
        methods: {
            handleUploadSuccess(res, file) {
                let width = this.width
                let height = this.height
                if(res && res.code === 0){
                    if (
                        (!width || width <= 0 || parseInt(res.data.width) === width)
                        && (!height || height <= 0 || parseInt(res.data.height) === height)
                    ) {
                        this.$emit('input', res.data.url)
                        this.dispatch('ElFormItem', 'el.form.blur', [res.data.url]);
                        this.dispatch('ElFormItem', 'el.form.change', [res.data.url]);
                    } else {
                        this.$message.error('请上传图片尺寸为' + width + 'px*' + height + 'px且大小不能超过2MB的图片')
                        return false;
                    }
                }else{
                    this.$message.error(res.message || '文件上传失败');
                }
            },
            beforeAvatarUpload(file) {
                let imgTypes = ['image/png', 'image/jpeg', 'image/gif'];
                let size = file.size;
                if(imgTypes.indexOf(file.type) < 0){
                    this.$message.error('只能上传 png、jpg、jpeg或gif类型的图片');
                    return false;
                }
                if(size > 2 * 1024 * 1024){
                    this.$message.error('上传的图片不能大于2M');
                    return false;
                }
            }
        }
    }
</script>

<style scoped>

    .img {
        width: 180px;
        min-height: 30px;
        height: auto;
    }
    .uploader-icon {
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        font-size: 28px;
        color: #8c939d;
        width: 180px;
        /*height: 180px;*/
        line-height: 178px;
        text-align: center;
        min-height: 30px;
    }
    .img, .uploader-icon{
        border: 1px dashed #d9d9d9;
        border-radius: 6px;
        padding: 1px;
    }
    .img:focus, .img:hover, .uploader-icon:focus, .uploader-icon:hover{
        border-color:#409eff
    }
</style>