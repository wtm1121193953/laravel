<template>
    <!-- 运营中心列表项操作 -->
    <div>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '冻结' : '解冻'}}</el-button>
        <el-button v-if="!scope.row.account" type="text" @click="showCreateAccountDialog = true">生成账户</el-button>
        <el-button v-if="scope.row.account" type="text" @click="showModifyAccountDialog = true">修改账户密码</el-button>
        <el-button type="text" @click="editMiniprogramDialog = true">{{!scope.row.miniprogram ? '配置小程序' : '修改小程序配置'}}</el-button>
        <el-button v-if="scope.row.miniprogram" type="text" @click="uploadCert">上传支付证书</el-button>

        <el-dialog title="编辑小程序配置信息" :visible.sync="editMiniprogramDialog">
            <miniprogram-form
                    :data="scope.row.miniprogram"
                    @cancel="editMiniprogramDialog = false"
                    @save="doEditMiniprogram"/>
        </el-dialog>

        <el-dialog title="编辑运营中心信息" :visible.sync="isEdit">
            <oper-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
        
        <el-dialog title="创建运营中心账户" :visible.sync="showCreateAccountDialog">
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

        <el-dialog v-if="scope.row.miniprogram" :visible.sync="showUploadCertDialog" title="上传支付证书">
            <el-form label-width="150px" size="small">
                <el-form-item label="上传支付证书">
                    <image-upload
                            list-type="text"
                            :action="certUploadUrl"
                            :limit="1"
                            :data="{miniprogramId: scope.row.miniprogram.id}"
                    >
                        <el-button>上传证书</el-button>
                    </image-upload>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import OperForm from './oper-form'
    import MiniprogramForm from '../miniprogram/miniprogram-form'

    export default {
        name: "oper-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
                isEdit: false,
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
                editMiniprogramDialog: false,
                showUploadCertDialog: false,
                certUploadUrl: '/api/admin/miniprogram/uploadCert'
            }
        },
        computed: {

        },
        methods: {
            edit(){
                this.isEdit = true;
            },
            doEdit(data){
                this.$emit('before-request')
                api.post('/oper/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                let opt = this.scope.row.status === 1 ? '冻结' : '解冻';
                this.$confirm(`确认要${opt}运营中心[ ${this.scope.row.name} ]吗?`).then( () => {
                    let status = this.scope.row.status === 1 ? 2 : 1;
                    this.$emit('before-request')
                    api.post('/oper/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                        this.scope.row.status = status;
                        this.$emit('change', this.scope.$index, data)
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            createAccount(){
                let data = this.accountForm;
                data.oper_id = this.scope.row.id;
                api.post('/oper_account/add', data).then(data => {
                    this.$alert('创建账户成功');
                    this.showCreateAccountDialog = false;
                    this.$emit('accountChanged', this.scope, data)
                })
            },
            modifyAccount(){
                let data = this.accountModifyPasswordForm;
                data.id = this.scope.row.account.id;
                data.oper_id = this.scope.row.id;
                api.post('/oper_account/edit', data).then(data => {
                    this.$alert('修改密码成功')
                    this.showModifyAccountDialog = false;
                    this.$emit('accountChanged', this.scope, data)
                })
            },
            doEditMiniprogram(data){
                this.isLoading = true;
                data.oper_id = this.scope.row.id;
                if(!this.scope.row.miniprogram){
                    api.post('/miniprogram/add', data).then((data) => {
                        this.editMiniprogramDialog = false;
                        this.$emit('miniprogramChanged', this.scope, data)
                    }).finally(() => {
                        this.isLoading = false;
                    })
                }else {
                    api.post('/miniprogram/edit', data).then((data) => {
                        this.editMiniprogramDialog = false;
                        this.$emit('miniprogramChanged', this.scope, data)
                    }).finally(() => {
                        this.isLoading = false;
                    })
                }
            },
            uploadCert(){
                this.showUploadCertDialog = true;
            }
        },
        created(){
        },
        components: {
            OperForm,
            MiniprogramForm
        }
    }
</script>

<style scoped>

</style>