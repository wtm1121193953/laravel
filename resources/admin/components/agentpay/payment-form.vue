<template>
    <el-row>

            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-col :span="11">
                    <el-form-item prop="name" label="名称">
                        <el-input v-model="form.name"/>
                    </el-form-item>
                    <el-form-item prop="status" label="状态">
                        <el-radio-group v-model="form.status">
                            <el-radio :label="1">启用</el-radio>
                            <el-radio :label="2">暂停</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
                <el-col :span="11" :offset="1">
                    <el-form-item prop="class_name" label="类名">
                        <el-input v-model="form.class_name" placeholder=""/>
                    </el-form-item>
                    <el-form-item prop="logo_url"  label="LOGO">
                        <el-upload
                                v-model="form.logo_url"
                                class="upload-demo"
                                action="/api/upload/image"
                                :limit="1"
                                :on-success="handleAvatarSuccess">
                            <el-button size="small" type="primary">点击上传</el-button>
                        </el-upload>
                    </el-form-item>
                </el-col>
                <el-col :span="22">

                <el-form-item prop="configs" label="配置信息">
                    <el-input type="textarea" :rows="5" v-model="form.configs" placeholder=""/>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
                </el-col>
            </el-form>

    </el-row>

</template>
<script>

    let defaultForm = {
        name: '',
        type: '',
        class_name: '',
        logo_url: '',
        status: 1,
        on_pc: 0,
        on_miniprogram: 0,
        on_app: 0,
        configs: ''
    };
    export default {
        name: 'payment-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){

            return {
                form: deepCopy(defaultForm),
                formRules: {
                    name: [
                        {required: true, message: '支付方式名称不能为空'},
                    ],
                    class_name: [
                        {required: true, message: '类名不能为空' },
                    ],
                },
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data);
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            resetForm(){
                this.$refs.form.resetFields();
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                    }
                })

            },
            handleAvatarSuccess(res, file) {
                this.form.logo_url = file.response.data.url;
            },
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

        }
    }
</script>
<style scoped>

</style>
