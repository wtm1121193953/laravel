<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="150px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="小程序配置名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="appid" label="小程序AppID">
                    <el-input v-model="form.appid"/>
                </el-form-item>
                <el-form-item prop="secret" label="小程序App Secret">
                    <el-input v-model="form.secret"/>
                </el-form-item>
                <el-form-item prop="mch_id" label="微信支付商户号">
                    <el-input v-model="form.mch_id"/>
                </el-form-item>
                <el-form-item prop="key" label="微信支付密钥">
                    <el-input v-model="form.key"/>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>

</template>
<script>
    let defaultForm = {
        name: '',
        appid: '',
        secret: '',
        mch_id: '',
        key: '',
    };
    export default {
        name: 'miniprogram-form',
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
                        {required: true, message: '名称不能为空'}
                    ],
                    appid: [
                        {required: true, message: 'App ID 名称不能为空'}
                    ],
                    secret: [
                        {required: true, message: 'App Secret 不能为空'}
                    ],
                    mch_id: [
                        {required: true, message: '微信支付商户号 不能为空'}
                    ],
                    key: [
                        {required: true, message: '微信支付密钥 不能为空'}
                    ],
                },
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data)
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
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
        }
    }
</script>
<style scoped>

</style>
