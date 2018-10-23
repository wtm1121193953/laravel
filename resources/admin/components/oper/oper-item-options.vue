<template>
    <!-- 运营中心列表项操作 -->
    <div>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="detail">查看</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '冻结' : '解冻'}}</el-button>
        <el-button v-if="!scope.row.account" type="text" @click="showCreateAccountDialog = true">生成帐号</el-button>
        <el-button v-if="scope.row.account" type="text" @click="showModifyAccountDialog = true">修改帐户密码</el-button>
        <el-button type="text" @click="editMiniprogramDialog = true">{{!scope.row.miniprogram ? '配置小程序' : '修改小程序配置'}}</el-button>
        <el-button v-if="scope.row.miniprogram" type="text" @click="uploadCert">上传支付证书</el-button>
        <el-button type="text" v-if="hasRule('/api/admin/oper/changePayToPlatform')" @click="showModifyPayToPlatformDialog = true">支付到平台设置</el-button>
        <el-button type="text" @click="bizerList">业务员</el-button>
        <el-button type="text" @click="showBizerDivide = true">业务员分成</el-button>

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
        
        <el-dialog title="创建运营中心帐户" :visible.sync="showCreateAccountDialog">
            <el-row>
                <el-col :span="16">
                    <el-form size="mini" :model="accountForm" :rules="accountFormRules" ref="form" label-width="150px">
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
                    <el-form size="mini" :model="accountModifyPasswordForm" :rules="accountModifyFormRules" ref="modifyPasswordForm" label-width="150px">
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

        <el-dialog v-if="scope.row.miniprogram" :visible.sync="showUploadCertDialog" title="上传支付证书">
            <el-form label-width="150px" size="small">
                <el-form-item label="上传支付证书">
                    <el-upload
                            list-type="text"
                            :action="certUploadUrl"
                            :limit="1"
                            :data="{miniprogramId: scope.row.miniprogram.id}"
                            :on-success="handleCertUploadSuccess"
                            :before-upload="beforeCertUpload"
                            :file-list="scope.row.miniprogram.cert_zip_path ? [{name: scope.row.miniprogram.cert_zip_path, url: scope.row.miniprogram.cert_zip_path}] : []"
                            :on-exceed="() => {$message.error('请先删除当前文件再上传')}"
                    >
                        <el-button>上传证书</el-button>
                    </el-upload>
                </el-form-item>
            </el-form>
        </el-dialog>

        <el-dialog title="确定支付到平台" width="30%" :visible.sync="showModifyPayToPlatformDialog">
            <el-row >
                <el-col :span="16">
                    <el-form size="mini" :model="payToPlatformForm" ref="modifyPayToPlatformForm" >
                        <el-form-item>
                            <div>
                                <el-radio v-model="payToPlatformForm.pay_to_platform" :label="0" :disabled="scope.row.pay_to_platform > 0">支付到运营中心</el-radio>
                            </div>
                            <div>
                                <el-radio v-model="payToPlatformForm.pay_to_platform" :label="1" :disabled="scope.row.pay_to_platform > 1">先切换到平台，平台不参与分成</el-radio>
                            </div>
                            <div>
                                <el-radio v-model="payToPlatformForm.pay_to_platform" :label="2">切换到平台，平台按照合约参与分成（此模式不支持修改）</el-radio>
                            </div>
                        </el-form-item>


                        <el-form-item>
                            <el-button @click="cancel">取消</el-button>
                            <el-button type="primary" @click="modifyPayToPlatform">确定</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>

        <el-dialog title="业务员分成比例" center :visible.sync="showBizerDivide" width="20%">
            <el-input-number size="small" v-model="bizerDivideNumber" :precision="2" :max="100" style="width: 80%"/>%
            <p class="tips">设置业务员的统一分成比例，包括已签约的</p>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="showBizerDivide = false">取 消</el-button>
                <el-button size="small" type="primary" @click="bizerDivide">确 定</el-button>
            </span>
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
                isEdit: false,
                showCreateAccountDialog: false,
                accountForm: {
                    account: '',
                    password: ''
                },
                accountFormRules: {
                    account: [
                        {required: true, message: '账号名不能为空'},
                        {validator: accountValidate}
                    ],
                    password: [
                        {required: true, min: 6, message: '密码不能为空且不能少于6位'},
                        {validator: passwordValidate}
                    ]
                },
                showModifyAccountDialog: false,
                showModifyPayToPlatformDialog: false,
                accountModifyPasswordForm: {
                    password: ''
                },
                accountModifyFormRules: {
                    password: [
                        {required: true, min: 6, message: '密码不能为空且不能少于6位'},
                        {validator: passwordValidate}
                    ]
                },
                payToPlatformForm: {
                    pay_to_platform: 0
                },
                editMiniprogramDialog: false,
                showUploadCertDialog: false,
                certUploadUrl: '/api/admin/miniprogram/uploadCert',

                showBizerDivide: false,
                bizerDivideNumber: this.scope.row.bizer_divide,
            }
        },
        computed: {

        },
        methods: {
            edit(){
                router.push({
                    path: '/oper/edit',
                    query: {id: this.scope.row.id}
                });
            },
            detail() {
                router.push({
                    path: '/oper/detail',
                    query: {id: this.scope.row.id}
                });
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
                this.$refs.form.validate(valid => {
                    if (valid) {
                        api.post('/oper_account/add', data).then(data => {
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
                data.oper_id = this.scope.row.id;
                this.$refs.modifyPasswordForm.validate(valid => {
                    if (valid) {
                        api.post('/oper_account/edit', data).then(data => {
                            this.$alert('修改密码成功')
                            this.showModifyAccountDialog = false;
                            this.$emit('accountChanged', this.scope, data)
                        })
                    }
                });
            },
            modifyPayToPlatform(){

                this.$confirm(`确认要修改吗?`).then( () => {
                    let pay_to_platform = this.payToPlatformForm.pay_to_platform;
                    api.post('/oper/changePayToPlatform', {id: this.scope.row.id, pay_to_platform: pay_to_platform}).then((data) => {
                        this.$alert('修改设置成功')
                        this.showModifyPayToPlatformDialog = false;
                        this.$emit('change', this.scope.$index, data)
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            cancel(){
                this.showModifyPayToPlatformDialog = false;
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
            },

            handleCertUploadSuccess(res, file, fileList) {
                if(res && res.code === 0){
                    file.name = file.url = res.data.path;
                    this.$emit('miniprogramChanged', this.scope, data)
                }else  {
                    fileList.forEach(function (item, index) {
                        if(item === file){
                            fileList.splice(index, 1)
                        }
                    });
                    this.$message.error(res.message || '文件上传失败');
                }
            },
            beforeCertUpload(file) {
                let imgTypes = ['application/zip', 'application/x-zip-compressed'];
                let size = file.size;
                if(imgTypes.indexOf(file.type) < 0){
                    this.$message.error('只能上传 zip 格式的文件');
                    return false;
                }
                if(size > 2 * 1024 * 1024){
                    this.$message.error('上传的文件不能大于2M');
                    return false;
                }
            },
            payToPlatform() {
                this.$confirm('确认支付到平台后不可更改，是否继续？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                }).then(() => {
                    api.post('/oper/setPayToPlatformStatus', {id: this.scope.row.id}).then(() => {
                        this.scope.row.pay_to_platform = 1;
                        this.$message.success('成功修改为支付到平台');
                    })
                }).catch(() => {
                    this.$message.info('已取消');
                })
            },
            bizerDivide() {
                let param = {
                    id: this.scope.row.id,
                    bizerDivide: this.bizerDivideNumber,
                }
                api.post('/oper/setOperBizerDivide', param).then(data => {
                    this.showBizerDivide = false;
                    this.$message.success('分成比例设置成功');
                })
            },
            bizerList() {
                router.push({
                    path: '/oper/bizer/list',
                    query: {
                        operId: this.scope.row.id,
                    }
                })
            }
        },
        mounted(){
            this.payToPlatformForm.pay_to_platform = this.scope.row.pay_to_platform
        },
        watch: {
            'scope.row': {
                deep: true,
                handler(){
                    this.payToPlatformForm.pay_to_platform = this.scope.row.pay_to_platform
                }
            }
        },
        components: {
            OperForm,
            MiniprogramForm
        }
    }
</script>

<style scoped>

</style>