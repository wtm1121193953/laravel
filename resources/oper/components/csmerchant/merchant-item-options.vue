<template>
    <!-- 商户列表项操作 v-if="parseInt(scope.row.audit_status) !== 1" -->
    <div>
        <el-button  type="text" @click="showMearchant(scope)">查看</el-button>
        <el-button v-if="parseInt(scope.row.audit_status)!==0" type="text" @click="edit(scope)">重新提交审核</el-button>
        <el-button v-if="parseInt(scope.row.audit_status)!==0" type="text" @click="seeAuditRecords">查看审核记录</el-button>
        <el-button v-if="parseInt(scope.row.audit_status)!==0 && parseInt(scope.row.audit_status)!==2" type="text" @click="changeStatus">{{parseInt(scope.row.status) === 1 ? '冻结' : ''}}</el-button>
        <el-button  style=" margin-left: 0px;" v-if="!scope.row.account && parseInt(scope.row.audit_status)!==0 && parseInt(scope.row.audit_status)!==2" type="text" @click="showCreateAccountDialog = true">生成帐户</el-button>
        <!--<el-button  style=" margin-left: 0px;" v-if="scope.row.account" type="text" @click="showModifyAccountDialog = true">修改帐户密码</el-button>-->
        <el-dialog title="创建商户帐号" :visible.sync="showCreateAccountDialog">
            <el-row>
                <el-col :span="16">
                    <el-form size="mini" ref="form" :model="accountForm" :rules="accountFormRules" label-width="150px">
                        <el-form-item label="帐户名" prop="account">
                            <el-input v-model="accountForm.account" placeholder="请输入帐户"/>
                        </el-form-item>
                        <el-form-item label="密码" prop="password">
                            <el-input type="password" v-model="accountForm.password" placeholder="请输入密码"/>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="createAccount">确定</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>

        <el-dialog title="修改帐户密码" v-if="scope.row.account" :visible.sync="showModifyAccountDialog">
            <el-row>
                <el-col :span="16">
                    <el-form size="mini" ref="modifyPasswordForm" :model="accountModifyPasswordForm" :rules="accountModifyFormRules" label-width="150px">
                        <el-form-item label="帐户名" prop="account">
                            <div>{{scope.row.account.account}}</div>
                        </el-form-item>
                        <el-form-item label="密码" prop="password">
                            <el-input type="password" v-model="accountModifyPasswordForm.password" placeholder="请输入密码"/>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="modifyAccount">确定</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from './merchant-form'

    export default {
        name: "merchant-item-options",
        props: {
            scope: {type: Object, required: true},
            query: {type: Object}
        },
        data(){
            let accountValidate = (rule, value, callback) => {
                if ((/\s+/.test(value))) {
                    callback(new Error('帐户名不允许有空格'));
                }else {
                    callback();
                }
            };
            let passwordValidate = (rule, value, callback) => {
                if ((/\s+/.test(value))) {
                    callback(new Error('密码不允许有空格'));
                }else {
                    callback();
                }
            };
            return {
                showCreateAccountDialog: false,
                accountForm: {
                    account: '',
                    password: ''
                },
                accountFormRules: {
                    account: [
                        {required: true, message: '帐号名不能为空'},
                        {validator: accountValidate}
                    ],
                    password: [
                        {required: true, min: 6, max: 18, message: '密码不能为空, 6-18位密码'},
                        {validator: passwordValidate}
                    ]
                },
                showModifyAccountDialog: false,
                accountModifyPasswordForm: {
                    password: ''
                },
                accountModifyFormRules: {
                    password: [
                        {required: true, min: 6, max: 18, message: '密码不能为空, 6-18位密码'},
                        {validator: passwordValidate}
                    ]
                },
            }
        },
        computed: {

        },
        methods: {
            edit(scope){
                let self = this;
                router.push({
                    path: '/cs/merchant/edit',
                    query: {id: scope.row.id,type: 'merchant-list'},
                })
                return false;
            },
            seeAuditRecords(){
                this.$menu.change('/cs/merchant/audit/list', {merchantId: this.scope.row.id})
            },
            showMearchant(scope){
                router.push({
                    path: '/cs/merchant/detail',
                    query: {id: scope.row.id},
                })
                return false;

            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/cs/merchant/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            createAccount(){
                let data = this.accountForm;
                data.merchant_id = this.scope.row.id;
                this.$refs.form.validate(valid => {
                    if (valid) {
                        api.post('/cs/merchant/createAccount', data).then(data => {
                            this.$alert('创建帐户成功');
                            this.showCreateAccountDialog = false;
                            this.$emit('accountChanged', this.scope, data)
                        })
                    }
                })
            },
            modifyAccount(){
                let data = this.accountModifyPasswordForm;
                data.id = this.scope.row.account.id;
                data.merchant_id = this.scope.row.id;
                this.$refs.modifyPasswordForm.validate(valid => {
                    if (valid) {
                        api.post('/cs/merchant/editAccount', data).then(data => {
                            this.$alert('修改密码成功')
                            this.showModifyAccountDialog = false;
                            this.$emit('accountChanged', this.scope, data)
                        })
                    }
                })
            },
        },
        components: {
            MerchantForm
        }
    }
</script>

<style scoped>

</style>