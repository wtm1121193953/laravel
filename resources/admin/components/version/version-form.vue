<template>
    <el-row>

            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-col :span="11">
                <el-form-item prop="app_name" label="应用名称">
                    <el-input v-model="form.app_name"/>
                </el-form-item>
                <el-form-item prop="version_no" label="版本号">
                    <el-input v-model="form.version_no" placeholder=""/>
                </el-form-item>
                </el-col>
                <el-col :span="11" :offset="1">
                <el-form-item prop="app_tag" label="版本标签">
                    <el-input v-model="form.app_tag" placeholder=""/>
                </el-form-item>
                <el-form-item label="版本序号" prop="version_seq">
                    <el-input-number v-model="form.version_seq" :min="0" :max="9999" v-if="form.id" disabled />
                </el-form-item>
                </el-col>
                <el-col :span="22">
                <el-form-item prop="desc" label="更新说明">
                    <el-input type="textarea" :rows="5" v-model="form.desc" placeholder=""/>
                </el-form-item>
                <el-form-item prop="status" label="发布状态">
                    <el-radio-group v-model="form.status">
                        <el-radio :label="1">暂不发布</el-radio>
                        <el-radio :label="2">已发布</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="force" label="强制更新">
                    <el-radio-group v-model="form.force">
                        <el-radio :label="0">否</el-radio>
                        <el-radio :label="1">是</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="app_type" label="应用类型">
                    <el-radio-group v-model="form.app_type" v-if="form.id" disabled>
                        <el-radio :label="1">Android</el-radio>
                        <el-radio :label="2">IOS</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="package_url" v-if="form.app_type == 1" required label="安装包">
                    <el-upload
                            v-model="form.package_url"
                            class="upload-demo"
                            action="/api/upload/file"
                            :limit="1"
                            :on-success="handleAvatarSuccess"
                            :before-upload="beforeAvatarUpload">
                        <el-button size="small" type="primary">点击上传</el-button>
                        <div slot="tip" class="el-upload__tip">只能上传apk文件</div>
                    </el-upload>
                </el-form-item>
                <el-form-item prop="app_size" v-if="form.app_type == 1" required label="版本大小">
                    <el-input v-model="form.app_size" :disabled="true"/>
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
        app_name: '',
        app_tag: '',
        version_no: '',
        version_seq: 1,
        desc: '',
        package_url: '',
        status: 1,
        force: 0,
        app_type: 1,
        app_size: 0,
    };
    export default {
        name: 'version-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){

            return {
                form: deepCopy(defaultForm),
                formRules: {
                    app_name: [
                        {required: true, message: '应用名称不能为空'},
                        {max: 20, message: '应用名称不能超过20个字'},
                    ],
                    app_tag: [
                        {required: true, message: '版本标签不能为空' },
                        {max: 30, message: '版本标签不能超过30个字'},
                    ],
                    version_no: [
                        {required: true, message: '版本号不能为空'},
                    ],
                    version_seq: [
                        {required: true, message: '版本序号不能为空'},
                    ],
                    desc: [
                        {required: true, message: '更新说明不能为空'},
                        {max: 30, message: '更新说明不能超过30个字'},
                    ],
                    status: [
                        {required: true, message: '发布状态不能为空'},
                    ],
                    force: [
                        {required: true, message: '强制更新不能为空'},
                    ],
                    app_type: [
                        {required: true, message: '应用类型不能为空'},
                    ],
                    package_url : [
                        {required: true, message: '安装包不能为空'},
                    ]
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
                this.form.package_url = file.response.data.url;
                this.form.app_size = parseFloat(file.response.data.size/1024/1024).toFixed(2);
            },
            beforeAvatarUpload(file) {
                const isAPK = file.type === 'application/vnd.android.package-archive';

                if (!isAPK) {
                    //this.$message.error('上传文件只能是 apk 格式!');
                }
                return true;
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

        }
    }
</script>
<style scoped>

</style>
