<template>
    <page title="TPS会员帐号管理">
        <div>
            <div v-if="bindInfo == null" class="title"><el-button type="primary" @click="showBox = true">生成TPS帐号</el-button></div>
            <div v-if="bindInfo" class="title">已生成TPS帐号：{{bindInfo.tps_account}}</div>
            <div class="tips m-t-20">
                <div class="tip">温馨提示：</div>
                <div class="tip">1、生成TPS帐号后，您在大千生活的下级用户对您贡献的消费额可以按系数转化成TPS消费额。</div>
                <div class="tip">2、大千生活消费额置换TPS积分公式为：大千消费额/6/6.5/4，例如600大千消费额可以置换TPS积分=600/6/6.5/4=3.84个</div>
                <div class="tip">3、大千消费额与TPS消费额置换比为6：1</div>
            </div>
        </div>

        <el-dialog :visible.sync="showBox" width="60%" title="生成TPS帐号" :closeOnClickModal="false">
			<el-row>
			    <el-col :span="15">
			        <el-form :model="form" label-width="80px" @submit.native.prevent ref="form" :rules="formRules">
                        <el-form-item prop="email" label="电子邮箱">
                            <el-input v-model="form.email"/>
                            <div class="tips">仅限后缀@shoptps.com官方邮箱注册，若没有邮箱，请联系客服</div>
                        </el-form-item>

                        <el-form-item prop="verifyCode" label="验证码">
                            <el-input v-model="form.verifyCode" style="width: 300px"/>
                            <el-button @click="sendVerifyCode" :disabled="verifyCodeSecond > 0" class="m-l-15">
                                <span v-if="verifyCodeSecond <= 0">获取验证码</span>
                                <span v-else>{{verifyCodeSecond}}秒</span>
                            </el-button>
                        </el-form-item>

                        <el-form-item>
			                <el-button type="primary" @click="genAccount()">生成</el-button>
			            </el-form-item>
			            
			        </el-form>
			    </el-col>
			</el-row>

        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "tps-bind",
        data(){
            return {
                bindInfo: '',
                showBox: false,
                form: {
                    email: '',
                    verifyCode: '',
                },
                verifyCodeSecond: 0,
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
            
            init(){
                api.get('/tps/getBindInfo').then(data => {
                    this.bindInfo = data;
                })
            },
            genAccount(){
                this.$refs.form.validate((valid) => {
                    if(valid){

                        if (!this.form.email){
                            this.$message.error('邮箱不能为空!');
                            return;
                        }
                        if(!this.form.email.match(/.*@shoptps\.com$/)){
                            this.$message.error('邮箱格式错误, 后缀必须是 @shoptps.com !')
                            return;
                        }
                        this.$confirm(
                            '每个运营中心仅只能添加一次TPS会员帐号，之后不可修改。确定生成吗？',
                            '提示',
                            {type: 'warning',}
                        ).then(() => {
                            api.post('/tps/bindAccount', this.form).then((data) => {
                                this.$alert('创建tps帐号成功, tps帐号默认登陆密码为 a12345678, 请及时修改');
                                this.showBox = false;
                                this.init();
                                this.bindInfo = data;
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
        		if(!this.form.email.match(/.*@shoptps\.com$/)){
        		    this.$message.error('邮箱格式错误, 后缀必须是 @shoptps.com !')
                    return;
                }
				api.post('/tps/sendVerifyCode', {email : this.form.email}).then(() => {
                    this.verifyCodeSecond = 60;
				    this.$message.success('邮件发送成功');
				    let interval = setInterval(() => {
				        this.verifyCodeSecond --;
				        if(this.verifyCodeSecond == 0){
				            clearInterval(interval)
                        }
                    }, 1000)
		        });

            }

        },
        created(){
            this.init();
        }
    }
</script>
