<template>
    <!-- 商户列表项操作 -->
    <div>
        <el-button type="text" @click="edit">重新提交资料</el-button>
        <el-button v-if="parseInt(scope.row.audit_status)!==0 && parseInt(scope.row.audit_status)!==2" type="text" @click="changeStatus">{{parseInt(scope.row.status) === 1 ? '冻结' : '解冻'}}</el-button>
        <el-button v-if="!scope.row.account && parseInt(scope.row.audit_status)!==0 && parseInt(scope.row.audit_status)!==2" type="text" @click="showCreateAccountDialog = true">生成账户</el-button>
        <el-button v-if="scope.row.account" type="text" @click="showModifyAccountDialog = true">修改账户密码</el-button>


        <el-dialog title="创建商户账号" :visible.sync="showCreateAccountDialog">
            <el-row>
                <el-col :span="16">
                    <el-form size="mini" :model="accountForm" :rules="accountFormRules" label-width="150px">
                        <el-form-item label="账户名" prop="account">
                            <el-input v-model="accountForm.account" placeholder="请输入账户"/>
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

        <el-dialog title="修改账户密码" v-if="scope.row.account" :visible.sync="showModifyAccountDialog">
            <el-row>
                <el-col :span="16">
                    <el-form size="mini" :model="accountModifyPasswordForm" :rules="accountModifyFormRules" label-width="150px">
                        <el-form-item label="账户名" prop="account">
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
            scope: {type: Object, required: true}
        },
        data(){
            return {
                showCreateAccountDialog: false,
                accountForm: {
                    account: '',
                    password: ''
                },
                accountFormRules: {
                    account: [
                        {required: true, message: '账号名不能为空'},
                    ],
                    password: [
                        {required: true, min: 6, message: '密码不能为空且不能少于6位'}
                    ]
                },
                showModifyAccountDialog: false,
                accountModifyPasswordForm: {
                    password: ''
                },
                accountModifyFormRules: {
                    password: [
                        {required: true, min: 6, message: '密码不能为空且不能少于6位'}
                    ]
                },
            }
        },
        computed: {

        },
        methods: {
            edit(){
                router.push({
                    path: '/merchant/edit',
                    query: {
                        id: this.scope.row.id,
                        type: 'merchant-list'
                    }
                })
            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/merchant/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            createAccount(){
                let data = this.accountForm;
                data.merchant_id = this.scope.row.id;
                api.post('/merchant/createAccount', data).then(data => {
                    this.$alert('创建账户成功');
                    this.showCreateAccountDialog = false;
                    this.$emit('accountChanged', this.scope, data)
                })
            },
            modifyAccount(){
                let data = this.accountModifyPasswordForm;
                data.id = this.scope.row.account.id;
                data.merchant_id = this.scope.row.id;
                api.post('/merchant/editAccount', data).then(data => {
                    this.$alert('修改密码成功')
                    this.showModifyAccountDialog = false;
                    this.$emit('accountChanged', this.scope, data)
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