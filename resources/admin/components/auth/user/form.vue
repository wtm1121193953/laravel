<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form">
                <el-form-item prop="username" label="用户名">
                    <el-input v-model="form.username"/>
                </el-form-item>
                <el-form-item v-if="!form.id" prop="password" label="密码">
                    <el-input v-model="form.password" type="password"/>
                </el-form-item>
                <el-form-item prop="group_id" label="所属角色">
                    <el-select v-model="form.group_id" placeholder="请选择角色">
                        <el-option
                                v-for="group in groups"
                                :key="group.id"
                                :label="group.name"
                                :value="group.id"/>
                    </el-select>
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
<script>
    import {mapState} from 'vuex'
    export default {
        name: 'user-form',
        props: {
            user: Object
        },
        data(){
            return {
                form: {
                    username: '',
                    password: '',
                    group_id: '',
                    status: 1,
                },
                formRules: {
                    username: [
                        {required: true, message: '名称不能为空'}
                    ]
                }
            }
        },
        computed: {
            ...mapState('auth', [
                'groups'
            ])
        },
        methods: {
            initForm(){
                if(this.user){
                    this.form = deepCopy(this.user)
                }else {
                    this.form = {
                        username: '',
                        password: '',
                        group_id: '',
                        status: 1,
                    }
                }
            },
            cancel(){
                this.$emit('cancel');
                this.$refs.form.resetFields();
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        this.$emit('save', deepCopy(this.form));
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
            user(){
                this.initForm();
            }
        }
    }
</script>
<style scoped>

</style>
