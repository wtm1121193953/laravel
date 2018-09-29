<template>
    <page title="提现设置" v-loading="pageLoading">
        <el-col :span="8">
            <el-alert
                v-if="withdrawAuditPrepare"
                title="审核中"
                type="info"
                :closable="false"
                show-icon>
            </el-alert>
            <el-alert
                v-if="withdrawAuditFailed"
                :title="form1.reason"
                type="error"
                :closable="false"
                show-icon>
            </el-alert>
            <el-tabs v-model="activeName" type="card" class="m-t-15">
                <el-tab-pane :disabled="activeName != 'uploadIdCard'" label="上传证件照" name="uploadIdCard">
                    <el-form :model="form1" :rules="formRules2" ref="form1" size="small" label-width="110px">
                        <el-form-item prop="name" label="身份证姓名">
                            <el-input v-model="form1.name" :disabled="withdrawAuditPrepare" clearable/>
                        </el-form-item>
                        <el-form-item prop="id_card_no" label="身份证号码">
                            <el-input v-model="form1.id_card_no" :disabled="withdrawAuditPrepare" clearable/>
                        </el-form-item>
                        <el-form-item prop="front_pic" label="身份证正面">
                            <image-upload v-model="form1.front_pic" :disabled="withdrawAuditPrepare" :limit="1"></image-upload>
                        </el-form-item>
                        <el-form-item prop="opposite_pic" label="身份证反面">
                            <image-upload v-model="form1.opposite_pic" :disabled="withdrawAuditPrepare" :limit="1"></image-upload>
                        </el-form-item>
                        <div class="tips">注意：提交审核后，将在7个工作日内返回审核结果，请耐心等待</div>
                        <el-form-item>
                            <el-button type="primary" v-if="!withdrawAuditPrepare" @click="uploadIdCardNext">提交审核</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :disabled="activeName != 'bindBankCard'" label="绑定银行卡" name="bindBankCard">
                    <el-form :model="form2" :rules="formRules1" ref="form2" size="small" label-width="110px">
                        <el-form-item prop="bank_card_no" label="提现银行卡号">
                            <el-input v-model="form2.bank_card_no" clearable/>
                        </el-form-item>
                        <el-form-item prop="bank_name" label="开户行">
                            <el-select v-model="form2.bank_name" filterable clearable placeholder="请选择">
                                <el-option
                                    v-for="item in bankList"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.name"
                                ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item prop="bank_card_open_name" label="账户姓名">
                            <el-input v-model="form2.bank_card_open_name" :disabled="true" clearable/>
                        </el-form-item>
                        <div class="tips">注意：业务员所获得的收入提现时，将通过以上所填写资料进行操作</div>
                        <el-form-item>
                            <el-button type="primary" @click="bindBankCardNext">下一步</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :disabled="activeName != 'settingPassword'" label="设置提现密码" name="settingPassword">
                    <el-form :model="form3" :rules="formRules3" ref="form3" size="small" label-width="110px">
                        <el-form-item prop="password" label="提现密码">
                            <el-input v-model="form3.password" type="password"  :maxlength="6" auto-complete="off" placeholder="6位纯数字密码"/>
                        </el-form-item>
                        <el-form-item prop="checkPassword" label="确认提现密码">
                            <el-input v-model="form3.checkPassword" type="password" :maxlength="6" auto-complete="off" placeholder="再次输入提现密码"/>
                        </el-form-item>
                        <div class="tips">注意：提现密码非常重要，请谨记！</div>
                        <el-form-item>
                            <el-button type="primary" @click="settingPasswordNext">确 定</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "withdraw-form",
        data() {
            let validateBankCardNo = (rule, value, callback) => {
                if (!(/^[0-9]\d{0,29}$/.test(value))) {
                    callback(new Error('请输入正确的提现银行卡号'));
                } else {
                    callback();
                }
            };
            let idCardNoValidate = (rule, value, callback) => {
                if (!(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(value))) {
                    callback(new Error('请输入正确的身份证号码'));
                }else {
                    callback();
                }
            };
            let validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                } else if (value.length !== 6) {
                    callback(new Error('密码需为6位'));
                } else {
                    if (this.form3.checkPassword !== '') {
                        this.$refs.form3.validateField('checkPassword');
                    }
                    callback();
                }
            };
            let validateCheckPass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (isNaN(value) || value.indexOf(' ') >= 0) {
                    callback(new Error('请输入数字值'));
                }  else if (value !== this.form3.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            return {
                activeName: 'uploadIdCard',

                form1: {
                    name: '',
                    id_card_no: '',
                    front_pic: '',
                    opposite_pic: '',
                },
                form2: {
                    bank_card_no: '',
                    bank_name: '',
                    bank_card_open_name: '',
                },
                form3: {
                    password: '',
                    checkPassword: '',
                },
                pageLoading: false,
                bankList: [],
                withdrawSettingSuccess: false,
                withdrawAuditPrepare: false,
                withdrawAuditFailed: false,

               formRules1: {
                    bank_card_no: [
                        {required: true, message: '提现银行卡号不能为空'},
                        {max: 35, min: 8, message: '提现银行卡号最少为8位，最多为35位'},
                        {validator: validateBankCardNo}
                    ],
                   bank_name: [
                       {required: true, message: '开户行不能为空'},
                   ],
                   bank_card_open_name: [
                       {required: true, message: '账户姓名不能为空'},
                       {max: 20, message: '账户姓名不能超过20个字'},
                   ],
               },
                formRules2: {
                    name: [
                        {required: true, message: '身份证姓名不能为空'},
                        {max: 20, message: '身份证姓名不能超过20个字'},
                    ],
                    id_card_no: [
                        {required: true, message: '身份证号码不能为空'},
                        {validator: idCardNoValidate},
                    ],
                    front_pic: [
                        {required: true, message: '身份证正面图片不能为空'},
                    ],
                    opposite_pic: [
                        {required: true, message: '身份证反面图片不能为空'},
                    ],
                },
                formRules3: {
                    password: [
                        {validator: validatePass}
                    ],
                    checkPassword: [
                        {validator: validateCheckPass}
                    ]
                },
            }
        },
        methods: {
            bindBankCardNext() {
                this.$refs.form2.validate(valid => {
                    if (valid) {
                        this.activeName = 'settingPassword';
                    }
                })
            },
            uploadIdCardNext() {
                this.$refs.form1.validate(valid => {
                    if (valid) {
                        if (this.withdrawAuditFailed) {
                            api.post('/wallet/withdraw/editBizerIdentityAuditRecord', this.form1).then(data => {
                                this.$message.success('提交审核成功');
                                this.withdrawAuditPrepare = true;
                                this.withdrawAuditFailed = false;
                            })
                        } else {
                            api.post('/wallet/withdraw/addBizerIdentityAuditRecord', this.form1).then(data => {
                                this.$message.success('提交审核成功');
                                this.withdrawAuditPrepare = true;
                                this.withdrawAuditFailed = false;
                            })
                        }
                    }
                });
            },
            settingPasswordNext() {
                this.$refs.form3.validate(valid => {
                    if (valid) {
                        let form = {};
                        Object.assign(form, this.form2, this.form3);
                        api.post('/wallet/withdraw/setting', form).then(data => {
                            this.$message.success('提现设置成功');
                            router.replace('/withdraw/password/success');
                        })
                    }
                })
            },
            init() {
                this.getBizerIdentityAuditRecord();
                this.getBankList();
            },
            getBankList() {
                api.get('/wallet/bank/list').then(data => {
                    this.bankList = data;
                })
            },
            getBizerIdentityAuditRecord() {
                this.pageLoading = true;
                api.get('/wallet/withdraw/getRecordAndWallet').then(data => {
                    let record = data.record;
                    if (record && record.id) {
                        if (record.status == 1) {
                            // 待审核
                            this.form1 = record;
                            this.activeName = 'uploadIdCard';
                            this.withdrawAuditPrepare = true;
                        } else if (record.status == 2) {
                            // 审核成功
                            this.form2.bank_card_open_name = record.name;
                            this.withdrawSettingSuccess = true;
                            if (data.isSetPassword) {
                                router.replace('/withdraw/password/success');
                            } else {
                                this.activeName = 'bindBankCard';
                            }
                        } else if (record.status == 3) {
                            // 审核失败
                            this.form1 = record;
                            this.activeName = 'uploadIdCard';
                            this.withdrawAuditFailed = true;
                        } else {
                            this.$message.error('改审核状态不存在');
                            return;
                        }
                    }
                    this.pageLoading = false;
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