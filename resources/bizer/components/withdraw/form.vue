<template>
    <el-row :gutter="20" v-loading="formLoading">
        <el-form :model="form" :rules="formRules" ref="form" label-width="100px" label-position="left" size="small">
            <el-col :span="12">
                <el-form-item label="可提现金额">
                    {{initForm.balance}}
                </el-form-item>
                <el-form-item prop="amount" label="提现金额">
                    <el-input-number v-model="form.amount" :precision="2" :min="0" :max="parseFloat(initForm.balance)"/>
                    <span>手续费{{chargeAmount}}</span>
                </el-form-item>
                <el-form-item prop="withdrawPassword" label="提现密码">
                    <el-input v-model="form.withdrawPassword" type="password" :maxlength="6" placeholder="6位纯数字密码" class="w-150"/>
                </el-form-item>
                <el-form-item label="提现账户名">
                    {{initForm.bankCardOpenName}}
                </el-form-item>
            </el-col>
            <el-col :span="12">
                <el-form-item label="提现银行卡号">
                    {{initForm.bankCardNo}}
                </el-form-item>
                <el-form-item label="开户行">
                    {{initForm.bankName}}
                </el-form-item>
                <el-form-item label="到账金额">
                    {{remitAmount}}
                </el-form-item>
            </el-col>
            <el-col style="text-align: center">
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="commit">立即申请</el-button>
            </el-col>
        </el-form>
    </el-row>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data() {
            let validateWithdrawPassword = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入提现密码'));
                } else if (isNaN(value)) {
                    callback(new Error('请输入数字值'));
                } else {
                    callback();
                }
            };
            let validateAmount = (rule, value, callback) => {
                if (value < 100) {
                    callback(new Error('提现金不能小于100'));
                } else {
                    callback();
                }
            };

            return {
                initForm: {
                    balance: 0,
                    bankCardOpenName: '',
                    bankCardNo: '',
                    bankName: '',
                    ratio: 0,  // 手续费百分比
                },
                form: {
                    amount: 0,
                    withdrawPassword: '',
                },
                formLoading: false,
                noWithdraw: true,

                formRules: {
                    amount: [
                        {validator: validateAmount}
                    ],
                    withdrawPassword: [
                        {validator: validateWithdrawPassword}
                    ],
                }
            }
        },
        computed: {
            //手续费
            chargeAmount() {
                if (this.form.amount == undefined) this.form.amount = 0;
                return (this.form.amount * this.initForm.ratio / 100).toFixed(2);
            },
            //到账金额
            remitAmount() {
                if (this.form.amount == undefined) this.form.amount = 0;
                return (this.form.amount * (1 - this.initForm.ratio / 100)).toFixed(2);
            },
        },
        methods: {
            init() {
                this.formLoading = true;
                api.get('/wallet/withdraw/getWithdrawInfoAndBankInfo').then(data => {
                    this.initForm = data;
                    this.formLoading = false;
                })
            },
            cancel() {
                this.$emit('closeWithdrawDialog');
            },
            commit() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        api.post('/wallet/withdraw/withdrawApplication', this.form).then(data => {
                            this.$message.success('提现申请成功');
                            this.$emit('closeWithdrawDialog');
                            this.$emit('getList');
                            this.$refs.form.resetFields();
                        })
                    }
                })
            },
            canWithdraw() {
                let day = (new Date()).getDate();
                if (day == 10 || day == 20 || day == 30) {
                    this.noWithdraw = false;
                } else {
                    this.noWithdraw = true;
                }
            },
        },
        created() {
            this.init();
            this.canWithdraw();
        }
    }
</script>

<style scoped>

</style>