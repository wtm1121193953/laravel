<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form">
                <el-form-item prop="name" label="权限名">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="url" label="url">
                    <el-input v-model="form.url"/>
                </el-form-item>
                <el-form-item prop="url_all" label="url_all">
                    <el-input type="textarea" :autosize="{ minRows: 2}" v-model="form.url_all"/>
                </el-form-item>
                <el-form-item prop="status" label="状态">
                    <el-radio-group v-model="form.status">
                        <el-radio :label="1">正常</el-radio>
                        <el-radio :label="2">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="sort" label="排序">
                    <el-input-number v-model="form.sort"/>
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
    export default {
        name: 'rule-form',
        props: {
            rule: Object
        },
        data(){
            return {
                form: {
                    name: '',
                    url: '',
                    url_all: '',
                    status: 1,
                    sort: 1
                },
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                }
            }
        },
        methods: {
            initForm(){
                if(this.rule){
                    this.form = deepCopy(this.rule)
                    this.form.url_all = this.form.url_all.split(',').join('\n')
                }else {
                    this.form = {
                        name: '',
                        url: '',
                        url_all: '',
                        status: 1,
                        sort: 1
                    }
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let rule = deepCopy(this.form);
                        rule.url_all = rule.url_all.split("\n").join(',')
                        this.$emit('save', rule);
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
            rule(val){
                this.initForm();
            }
        }
    }
</script>
<style scoped>

</style>
