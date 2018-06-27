<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="分类名称" label-width="100px">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="status" label="状态" label-width="100px">
                    <el-radio-group v-model="form.status">
                        <el-radio :label="1">正常</el-radio>
                        <el-radio :label="2">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-col style="text-align: center;">
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-col>
            </el-form>
        </el-col>
    </el-row>

</template>
<script>
    let defaultForm = {
        name: '',
        status: 1,
    };
    export default {
        name: 'dishes-category-form',
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
                        {required: true, message: '名称不能为空'},
                        {max: 10, message: '分类名称不能超过10个字'}
                    ]
                },
            }
        },
        methods: {
            initForm(){
                if(this.data.id){
                    this.form = deepCopy(this.data)
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
                this.reset();
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        this.form.name = this.form.name.trim();
                        if (!this.form.name) {
                            return false;
                        }
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                    }
                })

            },
            reset() {
                this.$refs.form.resetFields();
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
