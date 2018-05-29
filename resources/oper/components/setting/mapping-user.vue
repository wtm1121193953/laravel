<template>
    <page title="关联用户">
        <el-form :model="formData" v-if="!isBind">
            <el-form-item prop="mobile" label="关联电话号码" label-width="100px">
                <el-input v-model="formData.mobile" :maxlength="11" size="small" style="width: 150px"></el-input>
                <el-button type="success" @click="getVerifyCode" :disabled="isDisabled" size="small" style="margin-left: 10px">{{buttonName}}</el-button>
            </el-form-item>
            <el-form-item prop="verify_code" label="验证码" label-width="100px">
                <el-input v-model="formData.verify_code" :maxlength="4" size="small" style="width: 150px;"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" size="small" @click="commit">确认关联</el-button>
            </el-form-item>
        </el-form>

        <el-form v-else>
            <el-form-item label="关联电话号码" label-width="100px">
                <span>{{mobile}}</span>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "mapping-user",
        data() {
            return {
                formData: {
                    mobile: '',
                    verify_code: '',
                },
                isBind: false,
                mobile: '',
                buttonName: '获取验证码',
                isDisabled: false,
                time: 60,
            }
        },
        methods: {
            getVerifyCode() {
                let self = this;

                let reg=/^[1][3,4,5,7,8][0-9]{9}$/;
                if (!reg.test(self.formData.mobile)) {
                    self.$message.error('请输入有效手机号码');
                    return false;
                } else {
                    self.isDisabled = true;
                    let interval = window.setInterval(function() {
                        self.buttonName = '（' + self.time + '秒）后重新发送';
                        --self.time;
                        if(self.time < 0) {
                            self.buttonName = "获取验证码";
                            self.time = 60;
                            self.isDisabled = false;
                            window.clearInterval(interval);
                        }
                    }, 1000);

                    //获取验证码
                    api.get('sms/getVerifyCode', {mobile: self.formData.mobile}).then(() => {
                        self.$message.success('发送成功');
                    })
                }
            },
            commit() {
                if (this.formData.mobile == '' || this.formData.verify_code == '') {
                    this.$message.error('手机号码 或 验证码 不能为空');
                    return false;
                }
                this.$confirm('绑定此号码后将不能更改, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    api.post('/merchantBindUser', this.formData).then(data => {
                        this.$message.success('绑定成功');
                        this.mobile = data.userInfo.mobile;
                        this.isBind = true;
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消绑定'
                    });
                });
            },
            getUser(user_id) {
                api.get('/getUser', {id: user_id}).then(data => {
                    this.mobile = data.mobile;
                })
            },
            getMappingUser() {
                api.get('/getMappingUser').then(data => {
                    if (data == true){
                        this.isBind = true;
                        this.getUser(data.user_id);
                    }
                })
            }
        },
        created() {
            this.getMappingUser();
        }
    }
</script>

<style scoped>

</style>