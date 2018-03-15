<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="商品名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="supplier_id" label="所属供应商">
                    <el-select filterable placeholder="请选择供应商" v-model="form.supplier_id">
                        <el-option
                                v-for="supplier in suppliers"
                                :key="supplier.id"
                                :label="supplier.name"
                                :value="supplier.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="category_id" label="商品分类">
                    <el-select filterable placeholder="请选择商品分类" v-model="form.category_id">
                        <el-option
                                v-for="category in categories"
                                :key="category.id"
                                :label="category.name"
                                :value="category.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="pict_url" label="商品图片">
                    <image-upload v-model="form.pict_url" :limit="1"/>
                </el-form-item>
                <el-form-item prop="small_images" label="小图列表">
                    <image-upload v-model="form.small_images"/>
                </el-form-item>
                <el-form-item prop="detail" label="图文详情">
                    <quill-editor
                            v-model="form.detail"
                            ref="myQuillEditor"
                            :options="editorOption"
                            aria-placeholder="请输入内容"
                    />
                </el-form-item>

                <el-form-item prop="status" label="状态">
                    <el-radio-group v-model="form.status">
                        <el-radio :label="1">正常</el-radio>
                        <el-radio :label="2">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>

</template>
<style>
    /* 在dialog中的编辑器会被影响变形 */
    .quill-editor {
        line-height: normal;
    }
</style>
<script>
    // require styles
    import 'quill/dist/quill.core.css'
    import 'quill/dist/quill.snow.css'
    import 'quill/dist/quill.bubble.css'

    import { quillEditor } from 'vue-quill-editor'
    let defaultForm = {
        name: '',
        status: 1,
        supplier_id: '',
        category_id: '',
        pict_url: '',
        detail: '',
        small_images: '',
    };
    export default {
        name: 'item-form',
        props: {
            data: Object,
            suppliers: Array,
            categories: Array,
        },
        computed:{

        },
        data(){
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                },
                editorOption: {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{'header': 1}, {'header': 2}],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],
                            [{'indent': '-1'}, {'indent': '+1'}],
                            [{'direction': 'rtl'}],
                            [{'size': ['small', false, 'large', 'huge']}],
                            [{'header': [1, 2, 3, 4, 5, 6, false]}],
                            [{'font': []}],
                            [{'color': []}, {'background': []}],
                            [{'align': []}],
                            ['clean'],
                            ['link', 'image', 'video']
                        ],
                    }
                }
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data)
                }else {
                    this.form = deepCopy(defaultForm)
                }
                /*if(!this.form.small_images){
                    this.form.small_images = [];
                }else if(typeof this.form.small_images === 'string'){
                    this.form.small_images = this.form.small_images.split(',')
                } else if(!this.form.small_images[0]){
                    this.form.small_images = [];
                }*/
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                        setTimeout(() => {
                            this.$refs.form.resetFields();
                        }, 500)
                    }
                })

            }
        },
        created(){
            this.initForm();
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
            quillEditor
        }
    }
</script>
<style scoped>

</style>
