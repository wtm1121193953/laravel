<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form">
                <el-form-item prop="name" label="渠道名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="remark" label="备注">
                    <el-input v-model="form.remark"/>
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
        remark: '',
    }
    export default {
        name: "invite-channel-form",
        props: {
            data: Object,
        },
        data() {
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                }
            }
        },
        methods: {
            initForm() {
                if (this.data) {
                    this.form = deepCopy(this.data)
                } else {
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

            }
        },
        created(){
            this.initForm()
        },
        watch: {
            data(val){
                this.initForm();
            },
        }
    }
</script>

<style scoped>

</style>