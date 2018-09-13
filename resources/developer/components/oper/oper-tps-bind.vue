<template>

    <div>
        <el-button type="text" size="small" @click="showBox = true">绑定TPS帐号</el-button>

        <el-dialog :visible.sync="showBox" width="60%" title="绑定TPS帐号" :closeOnClickModal="false">
			<el-row>
			    <el-col :span="15">
			        <el-form :model="form" label-width="80px" @submit.native.prevent ref="form" :rules="formRules">
                        <el-form-item prop="email" label="电子邮箱">
                            <el-input v-model="form.email"/>
                            <div class="tips">仅限后缀@yunke138.com官方邮箱注册，若没有邮箱，请联系客服</div>
                        </el-form-item>

                        <el-form-item prop="verifyCode" label="验证码">
                            <el-input v-model="form.verifyCode" style="width: 300px"/>
                            <el-button :loading="verifyCodeBtnLoading" @click="sendVerifyCode" :disabled="verifyCodeSecond > 0" class="m-l-15">
                                <span v-if="verifyCodeSecond <= 0">获取验证码</span>
                                <span v-else>{{verifyCodeSecond}}秒</span>
                            </el-button>
                        </el-form-item>

                        <el-form-item>
			                <el-button type="primary" @click="genAccount()">绑定</el-button>
			            </el-form-item>
			            
			        </el-form>
			    </el-col>
			</el-row>

        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "oper-tps-bind",
        props: {
            scope: Object,
        },
        data(){
            return {
                //bindInfo: '',
                showBox: false,
                form: {
                    email: '',
                    verifyCode: '',
                },
                verifyCodeSecond: 0,
                verifyCodeBtnLoading: false,
                formRules: {
                    verifyCode: [
                        {required: true, message: '验证码不能为空' }
                    ],
                    email: [
                        {required: true, type: 'email', message: '请输入正确的邮箱'},
                    ],
                },

            }
        },
        methods: {

            genAccount(){
                this.$refs.form.validate((valid) => {
                    if(valid){

                        if (!this.form.email){
                            this.$message.error('邮箱不能为空!');
                            return;
                        }
                        if(!this.form.email.match(/.*@yunke138\.com$/)){
                            this.$message.error('邮箱格式错误, 后缀必须是 @yunke138.com !')
                            return;
                        }
                        this.$confirm(
                            '每个运营中心仅只能添加一次TPS会员帐号，之后不可修改。确定绑定吗？',
                            '提示',
                            {type: 'warning',}
                        ).then(() => {
                            this.form.operId = this.scope.row.id;
                            api.post('/tps/bindAccount', this.form).then((data) => {
                                //this.$alert('创建tps帐号成功, tps帐号默认登陆密码为 a12345678, 请及时修改');
                                this.showBox = false;
                                //this.bindInfo = data;
                                this.$emit('bound', data)
                            });
                        })
                    }
                })
            },

            sendVerifyCode(){
        		// 验证邮箱格式
        		if (!this.form.email){
        		    this.$message.error('邮箱不能为空!');
        		    return;
        		}
        		if(!this.form.email.match(/.*@yunke138\.com$/)){
        		    this.$message.error('邮箱格式错误, 后缀必须是 @yunke138.com !')
                    return;
                }
                this.verifyCodeBtnLoading = true;
				api.post('/tps/sendVerifyCode', {email : this.form.email}).then(() => {
                    this.verifyCodeSecond = 60;
				    this.$message.success('邮件发送成功');
				    let interval = setInterval(() => {
				        this.verifyCodeSecond --;
				        if(this.verifyCodeSecond == 0){
				            clearInterval(interval)
                        }
                    }, 1000)
		        }).finally(() => {
                    this.verifyCodeBtnLoading = false;
                });

            }

        },
        created(){
            //console.log(this.form)
        }
    }
</script>
