<template>
    <page title="我要提现" :breadcrumbs="{账户总览: '/wallet/summary/list'}">
        <el-col :span="7">
            <el-form :model="form" :rules="formRules" ref="form" v-loading="formLoading" label-width="100px">
                <el-form-item label="可提现金额">
                    {{initForm.balance}}
                </el-form-item>
                <el-form-item prop="amount" label="提现金额">
                    <el-input-number v-model="form.amount" :precision="2" :min="0" :max="parseFloat(initForm.balance)"/>
                    <div>手续费{{chargeAmount}}</div>
                </el-form-item>
                <el-form-item prop="withdrawPassword" label="提现密码">
                    <el-input v-model="form.withdrawPassword" type="password" :maxlength="6" placeholder="6位纯数字密码"/>
                </el-form-item>
                <el-form-item v-if="initForm.bankCardType == 1" prop="invoiceExpressCompany" label="发票快递公司">
                    <el-input v-model="form.invoiceExpressCompany"/>
                </el-form-item>
                <el-form-item v-if="initForm.bankCardType == 1" prop="invoiceExpressNo" label="快递单号">
                    <el-input v-model="form.invoiceExpressNo"/>
                </el-form-item>
                <el-form-item label="到账金额">
                    {{remitAmount}}
                </el-form-item>
                <el-form-item label="账户类型">
                    {{initForm.bankCardType == 1 ? '公账' : '私账'}}
                </el-form-item>
                <el-form-item label="提现账户名">
                    {{initForm.bankOpenName}}
                </el-form-item>
                <el-form-item label="提现银行卡号">
                    {{initForm.bankCardNo}}
                </el-form-item>
                <el-form-item label="开户行">
                    {{initForm.subBankName}}
                </el-form-item>
            </el-form>
        </el-col>
        <el-col>
            <div class="tips" style="display: inline;">
                <div style="float:left;">
                    <p>温馨提示： </p>
                </div>

                <div style="float:left;">
                    <ol>
                        <li><p>提现手续费{{initForm.ratio}}%</p></li>
                        <li v-if="initForm.bankCardType == 1">
                            <p>提现需要邮寄发票，发票总面额至少要大于提现额度，否则可能造成提现不成功</p>
                            <p>发票邮寄地址：深圳市福田区安化工业区4栋3楼 华翎科技有限公司  联系人：华翎财务部  电话 ：0755- 82563639</p>
                        </li>
                        <li><p>提现成功发起后，7个工作日左右到账。</p></li>
                        <li><p>每月10号、20号、30号才能提现</p></li>
                        <li><p>最低提现金额100元</p></li>
                    </ol>
                </div>
            </div>
        </el-col>
        <el-col>
            <el-button @click="cancel">取 消</el-button>
            <el-button type="primary" @click="commit">确 定</el-button>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-withdraw-form",
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
            let validateCompany = (rule, value, callback) => {
                if (this.initForm.bankCardType == 1) {
                    if (value === '') {
                        callback(new Error('发票快递公司不能为空'));
                    } else if (value.length > 30) {
                        callback(new Error('发票快递公司不能超过30个字'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            };
            let validateNo = (rule, value, callback) => {
                if (this.initForm.bankCardType == 1) {
                    if (value === '') {
                        callback(new Error('快递单号不能为空'));
                    } else if (value.length > 30) {
                        callback(new Error('快递单号不能超过30个字'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            };

            return {
                form: {
                    amount: 0,
                    withdrawPassword: '',
                    invoiceExpressCompany: '',
                    invoiceExpressNo: '',
                },
                initForm: {
                    balance: 0,  // 可提现金额
                    bankOpenName: '',
                    bankCardNo: '',
                    subBankName: '',
                    bankCardType: 1, // 账户类型 1-公司 2-个人
                    ratio: 0,  // 手续费百分比
                },
                formLoading: false,


                formRules: {
                    amount: [
                        {validator: validateAmount}
                    ],
                    withdrawPassword: [
                        {validator: validateWithdrawPassword}
                    ],
                    invoiceExpressCompany: [
                        {validator: validateCompany}
                    ],
                    invoiceExpressNo: [
                        {validator: validateNo}
                    ]
                }
            }
        },
        computed: {
            //手续费
            chargeAmount() {
                return (this.form.amount * this.initForm.ratio / 100).toFixed(2);
            },
            //到账金额
            remitAmount() {
                return (this.form.amount * (1 - this.initForm.ratio / 100)).toFixed(2);
            }
        },
        methods: {
            init() {
                this.formLoading = true;
                api.get('/wallet/withdraw/getWithdrawInfo').then(data => {
                    if (!data.isSetPassword) {
                        router.push({
                            path: '/wallet/withdraw/password',
                            query: {
                                redirect: '/wallet/withdraw/form'
                            }
                        });
                    }
                    this.initForm = data;
                    this.formLoading = false;
                })
            },
            cancel() {
                router.push('/wallet/summary/list');
            },
            commit() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        api.post('/wallet/withdraw/withdrawApplication', this.form).then(data => {
                            this.$message.success('提现申请成功');
                            this.$refs.form.resetFields();
                            router.push({
                                path: '/wallet/withdraw/success',
                                query: {
                                    id: data.id,
                                }
                            })
                        })
                    }
                })
            }
        },
        created() {
            this.init();
        }
    }
</script>

<style scoped>

</style>