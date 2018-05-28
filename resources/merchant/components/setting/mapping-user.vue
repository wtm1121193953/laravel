<template>
    <page title="关联用户">
        <el-form :model="formData">
            <el-form-item prop="mobile" label="关联电话号码" label-width="100px">
                <el-input v-model="formData.mobile" size="small" style="width: 200px"></el-input>
                <el-button type="success" @click="getVerifyCode" size="small" style="margin-left: 10px">获取验证码</el-button>
            </el-form-item>
            <el-form-item prop="verifyCode" label="验证码" label-width="100px">
                <el-input v-model="formData.verifyCode" size="small" style="width: 200px;"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="commit">确认关联</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    export default {
        name: "mapping-user",
        data() {
            return {
                formData: {
                    mobile: '',
                    verifyCode: '',
                }
            }
        },
        methods: {
            getVerifyCode() {

            },
            commit() {

            }
        }
    }
</script>

<style scoped>

</style>