<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px"  ref="info" @submit.native.prevent>
                <el-form-item label="提交认证时间">
                    {{info.created_at}}
                </el-form-item>
                <el-form-item prop="contacter" label="手机号">
                    {{info.user.mobile}}
                </el-form-item>
                <el-form-item prop="tel" label="用户ID">
                    {{info.user_id}}
                </el-form-item>

                <el-form-item prop="address" label="注册时间">
                    {{info.user.created_at}}
                </el-form-item>
                <el-form-item prop="email" label="姓名">
                    {{info.name}}
                </el-form-item>
                <el-form-item prop="legal_name" label="身份证号码">
                    {{info.number}}
                </el-form-item>
                <el-form-item label="身份证正面" prop="front_pic">
                    <div v-viewer>
                        <img v-if="info.front_pic" :src="info.front_pic" width="200px" height="100px" alt="开户许可证">
                    </div>
                </el-form-item>
                <el-form-item label="身份证反面" prop="opposite_pic">
                    <div v-viewer>
                        <img v-if="info.opposite_pic" :src="info.opposite_pic" width="200px" height="100px" alt="营业执照">
                    </div>
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
        name: 'identity-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                form: '',
            }
        },
        methods: {
            initForm(){

            },
            cancel(){
                this.$emit('cancel');
            },
            resetForm(){

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
