<template>
    <page title="TPS会员账号管理">
        <div>
            <div v-if="!bindAccount" class="title"><el-button @click="showBox = true">生成TPS账号</el-button></div>
            <div v-else class="title">{{bindAccount}}</div>
            <div class="tips">
                <div class="tip">温馨提示：</div>
                <div class="tip">1、生成TPS账号后，您在大千生活的下级用户对您贡献的消费额可以按系数转化成TPS消费额。</div>
                <div class="tip">2、大千生活消费额置换TPS积分公式为：大千消费额/6/6.5/4，例如600大千消费额可以置换TPS积分=600/6/6.5/4=3.84个</div>
                <div class="tip">3、大千消费额与TPS消费额置换比为6：1</div>
            </div>
        </div>
        <el-dialog :visible.sync="showBox" width="50%">

			<el-row>
			    <el-col :span="22">
			        <el-form :model="form" label-width="80px" @submit.native.prevent ref="form">
			        
			            <el-form-item prop="mail" label="电子邮箱">
			                <el-input v-model="form.mail"/>
			            </el-form-item>
			            
			            <el-form-item>
			                <el-button @click="cancel">取消</el-button>
			                <el-button type="primary" @click="showMsg()">保存</el-button>
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
        		bindAccount: '',
                showBox: false,
                test: '',
                form: {}
                
            }
        },
        methods: {
            
            init(){
                api.get('/api/oper/tps/getBindInfo').then(data => {
                    this.bindAccount = data.bindAccount;
                })
            },
            showMsg(){
                //todo
        	    this.$confirm('每个运营中心仅只能添加一次TPS会员账号，之后不可修改。确定生成吗？', '提示', {type: 'warning',}).then(() => {
        			api.post('/api/oper/tps/bindAccount').then((data) => {
        			    this.$message.success('保存成功:');
                        this.showBox = false;
                        this.init();
                        this.bindAccount = data.bindAccount;
        	        });}).catch(() => {
        	            this.$message({
        	              type: 'info',
        	              message: '已取消'
        	            });
        	        });                
            },
            
            cancel(){
        		// todo 
                this.showBox = false;
            }

        },
        created(){
            this.init();
        }
    }
</script>

<style scoped>

    .showBox-title {
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }

    .tips {
        margin-top: 20px;
        margin-left: 20px;
        color: #555;
        font-size: 14px;
        line-height: 24px;
    }
</style>