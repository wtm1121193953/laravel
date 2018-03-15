<template>
    <el-upload
            class="uploader"
            :action="action"
            list-type="picture-card"
            :file-list="fileList"
            :on-success="handleUploadSuccess"
            :before-upload="beforeUpload"
            :on-remove="handleRemove"
            :disabled="disabled"
            :limit="limit"
            :on-exceed="onExceed"
    >
        <i class="el-icon-plus"></i>
    </el-upload>
</template>

<script>
    import emitter from 'element-ui/src/mixins/emitter';
    /**
     * 多图片上传组件
     * 已完成功能:
     *  选项:
     *      value: 绑定的图片地址
     *      action: 图片上传服务器地址, 默认为: '/pub/upload/index'
     *      width: 图片宽度
     *      height: 图片高度
     *      checkSize: 是否检查图片宽高 (宽高都传入时才有效)
     *      limit: 限制高度
     *      disabled: 是否可删除, 禁用
     *  功能:
     *      图片上传功能
     *      删除按钮
     *  事件:
     *      before: 图片上传前, 如果上传前的前置条件检查不通过, 则不会触发, 只有在请求上传服务器时才触发
     *      success: 图片上传成功
     *      fail: 图片上传失败
     *      complete: 上传后(不区分成功与失败, 都会执行)
     */
    export default {
        name: "image-upload",
        props: {
            value: {type: Array|String},
            action: {type: String, default: '/api/upload/image'},
            width: {type: Number},
            height: {type: Number},
            checkSize: {type: Boolean, default: true},
            limit: {type: Number},
            disabled: {type: Boolean, default: false},
        },
        mixins: [emitter],
        data(){
            return {
                valueType: 'array',
                fileList: [],
            }
        },
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
            emitInput(){
                let value = [];
                this.fileList.forEach(item => {
                    value.push(item.url)
                })
                if(this.valueType === 'string'){
                    value = value.join(',');
                }
                this.$emit('input', value)
                this.dispatch('ElFormItem', 'el.form.blur', [value]);
                this.dispatch('ElFormItem', 'el.form.change', [value]);
            },
            handleRemove(file, fileList) {
                this.fileList = fileList;
                this.emitInput()
            },
            handleUploadSuccess(res, file, fileList) {
                let width = this.width
                let height = this.height
                if(res && res.code === 0){
                    if (
                        (!width || width <= 0 || parseInt(res.data.width) === width)
                        && (!height || height <= 0 || parseInt(res.data.height) === height)
                    ) {
                        file.url = res.data.url;
                        this.fileList = fileList;
                        this.emitInput();
                    } else {
                        this.$message.error('请上传图片尺寸为' + width + 'px*' + height + 'px且大小不能超过2MB的图片')
                        return false;
                    }
                    this.$emit('success')
                }else{
                    this.$message.error(res.message || '文件上传失败');
                    this.$emit('fail')
                }
                this.$emit('complete')
            },
            beforeUpload(file) {
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
                this.$emit('before')
            },
            onExceed(){
                this.$message.warning(`最多只能上传${this.limit}张图片`)
            }
        },
        created(){
            let value = [];
            if(typeof this.value === 'string'){
                this.valueType = 'string';
                if(this.value){
                    value = this.value.split(',')
                }
            }else {
                this.valueType = 'array';
                value = this.value;
            }
            value.forEach(item => {
                this.fileList.push({
                    url: item
                })
            })
        }
    }
</script>

<style scoped>
    .el-icon-close {
        position: absolute;
        right: 0;
        z-index: 1000;
        padding: 3px;
        margin: 3px;
        border: 1px dashed #d9d9d9;
        border-radius: 4px;
    }
    .el-icon-close:hover{
        border-color: #0e90d2;
    }
    .img-icon {
        position: relative;
    }
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