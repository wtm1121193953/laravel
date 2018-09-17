<template>
    <page title="提现设置">
        <el-col :span="8">
            <el-tabs v-model="activeName" type="card">
                <el-tab-pane :disabled="activeName != 'bindBankCard'" label="绑定银行卡" name="bindBankCard">
                    <el-form :model="form1" :rules="formRules1" ref="form1" size="small" label-width="110px">
                        <el-form-item prop="bank_card_no" label="提现银行卡号">
                            <el-input v-model="form1.bank_card_no" clearable/>
                        </el-form-item>
                        <el-form-item prop="bank_name" label="开户行">
                            <el-select v-model="form1.bank_name" filterable clearable placeholder="请选择">
                                <el-option
                                    v-for="item in bankList"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.name"
                                ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item prop="bank_card_open_name" label="账户姓名">
                            <el-input v-model="form1.bank_card_open_name" clearable/>
                        </el-form-item>
                        <div class="tips">注意：业务员所获得的收入提现时，将通过以上所填写资料进行操作</div>
                        <el-form-item>
                            <el-button type="primary" @click="bindBankCardNext">下一步</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :disabled="activeName != 'uploadIdCard'" label="上传证件照" name="uploadIdCard">
                    <el-form :model="form2" :rules="formRules2" ref="form2" size="small" label-width="110px">
                        <el-form-item prop="id_card_name" label="身份证姓名">
                            <el-input v-model="form2.id_card_name" clearable/>
                        </el-form-item>
                        <el-form-item prop="id_card_no" label="身份证号码">
                            <el-input v-model="form2.id_card_no" clearable/>
                        </el-form-item>
                        <el-form-item prop="front_pic" label="身份证正面">
                            <image-upload v-model="form2.front_pic" :limit="1"></image-upload>
                        </el-form-item>
                        <el-form-item prop="opposite_pic" label="身份证反面">
                            <image-upload v-model="form2.opposite_pic" :limit="1"></image-upload>
                        </el-form-item>
                        <div class="tips">注意：提交审核后，将在7个工作日内返回审核结果，请耐心等待</div>
                        <el-form-item>
                            <el-button type="primary" @click="uploadIdCardNext">提交审核</el-button>
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
                activeName: 'bindBankCard',

                form1: {
                    bank_card_no: '',
                    bank_name: '',
                    bank_card_open_name: '',
                },
                form2: {
                    id_card_name: '',
                    id_card_no: '',
                    front_pic: '',
                    opposite_pic: '',
                },
                form3: {
                    password: '',
                    checkPassword: '',
                },
                bankList: [],

               formRules1: {
                    bank_card_no: [
                        {required: true, message: '提现银行卡号不能为空'},
                        {max: 30, min: 8, message: '提现银行卡号最少为8位，最多为30位'},
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
                    id_card_name: [
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
                this.$refs.form1.validate(valid => {
                    if (valid) {
                        this.activeName = 'uploadIdCard';
                    }
                })
            },
            uploadIdCardNext() {
                this.$refs.form2.validate(valid => {
                    if (valid) {
                        this.activeName = 'settingPassword';
                    }
                });
            },
            settingPasswordNext() {
                this.$refs.form3.validate(valid => {
                    if (valid) {
                        let form = {};
                        Object.assign(form, this.form1, this.form2, this.form3);
                        api.post('/wallet/withdraw/setting', form).then(data => {
                            this.$message.success('提现设置成功');
                            router.replace(this.$route.path);
                        })
                    }
                })
            },
            init() {
                this.getBankList();
            },
            getBankList() {
                api.get('/wallet/bank/list').then(data => {
                    this.bankList = data;
                })
            },
        },
        created() {
            this.init();
        }
    }
</script>

<style scoped>

</style>