<template>
    <div>
        <el-upload
                class="uploader"
                :action="action"
                :list-type="listType"
                :file-list="fileList"
                :on-preview="preview ? handlePreview : null"
                :on-success="handleUploadSuccess"
                :on-error="handleError"
                :before-upload="beforeUpload"
                :on-remove="handleRemove"
                :on-change="handleChange"
                :disabled="disabled"
                :limit="limit"
                :data="data"
                :on-exceed="onExceed"
                :class="{'upload-fulled' : fileList.length >= limit}"
        >
            <i v-if="!$slots.default" class="el-icon-plus"></i>
            <slot/>
        </el-upload>
        <img-preview-dialog :url="previewImage" :visible.sync="isShow"/>
    </div>
</template>

<script>
    import ImgPreviewDialog from '../img/preview-dialog'
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
     *      listType: 图片列表类型: picture-card/picture/text, 默认: picture-card
     *      preview: 是否可预览图片
     *  功能:
     *      图片上传功能
     *      删除按钮
     *  事件:
     *      before: 图片上传前, 如果上传前的前置条件检查不通过, 则不会触发, 只有在请求上传服务器时才触发
     *      success: 图片上传成功
     *      fail: 图片上传失败
     *      complete: 上传后(不区分成功与失败, 都会执行)
     *      change: 文件状态改变时的钩子，添加文件、上传成功和上传失败时都会被调用, 第一个参数为file, 其中status会有ready,success,fail 三种状态
     *      error: 上传失败时的钩子
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
            listType: {type: String, default: 'picture-card'},
            preview: {type: Boolean, default: false},
            data: {type: Object, default: () => {}}
        },
        mixins: [emitter],
        data(){
            return {
                valueType: 'array',
                fileList: [],
                isShow: false,
                previewImage: '',
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
                    if(item.status == "success"){
                        // 兼容回显时的 item.url
                        value.push(item.response ? item.response.data.url : item.url);
                    }
                })
                if(this.valueType === 'string'){
                    value = value.join(',');
                }
                this.$emit('input', value)
                this.dispatch('ElFormItem', 'el.form.blur', [value]);
                this.dispatch('ElFormItem', 'el.form.change', [value]);
            },
            handlePreview(file){
                this.previewImage = file.url;
                this.isShow = true;
            },
            handleRemove(file, fileList) {
                this.fileList = fileList;
                this.$emit('remove');
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
                        this.fileList = fileList;
                        this.emitInput();
                        this.$emit('success')
                    } else {
                        fileList.forEach(function (item, index) {
                            if(item === file){
                                fileList.splice(index, 1)
                            }
                        })
                        this.$message.error('请上传图片尺寸为' + width + 'px*' + height + 'px且大小不能超过2MB的图片')
                        this.$emit('fail')
                    }
                }else{
                    fileList.forEach(function (item, index) {
                        if(item === file){
                            fileList.splice(index, 1)
                        }
                    })
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
            },
            handleChange(file){
                this.$emit('change', file);
            },
            handleError() {
                this.$emit('error');
                this.$emit('complete')
            },
            initFileList(){

                let value = [];
                if(typeof this.value === 'string'){
                    this.valueType = 'string';
                    if(this.value){
                        value = this.value.split(',')
                    }
                }else {
                    this.valueType = 'array';
                    value = this.value || [];
                }
                this.fileList = [];
                value.forEach(item => {
                    this.fileList.push({
                        url: item
                    })
                })
            }
        },
        created(){
            this.initFileList()
        },
        watch: {
            value (val){
                // this.initFileList()
            }
        },
        components: {
            ImgPreviewDialog
        }
    }
</script>

<style>
    .upload-fulled .el-upload--picture-card {
        display: none;
    }
</style>

<style scoped>
</style>