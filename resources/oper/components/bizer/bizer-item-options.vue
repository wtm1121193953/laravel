<template>
    <!-- 我的业务员列表项操作 -->
    <div>
        <el-button type="text" @click="remarks">备注</el-button>
        <el-button type="text" @click="merchants">业务</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.sign_status === 1 ? '冻结' : '解冻'}}</el-button>
        <el-button type="text" @click="dividedIntoSettings">分成设置</el-button>

        <el-dialog title="业务员备注" :visible.sync="dialogRemarksFormVisible" width="30%">
            <el-form :model="formRemarks" label-width="70px">
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="formRemarks.remark" auto-complete="off" placeholder="最多50个字" maxlength="50"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogRemarksFormVisible = false">取 消</el-button>
                <el-button type="primary"  v-loading="bntLoading" :disabled="bntLoading"  @click="submitRemarksForm">提 交</el-button>
            </div>
        </el-dialog>

        <el-dialog title="业务员分成" :visible.sync="dialogDividedIntoSettingsFormVisible" width="30%">
            <el-form :model="formDividedIntoSettings" :rules="divideRules" label-width="70px" ref="formDivided">
                <el-form-item label="分成" prop="divide">
                    <el-input v-model="formDividedIntoSettings.divide" auto-complete="off" style="width:90%;"/> %
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogDividedIntoSettingsFormVisible = false">取 消</el-button>
                <el-button type="primary" v-loading="bntLoading" :disabled="bntLoading"  @click="submitSettingsdivide">提 交</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            var validateDivided = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入分成'));
                  } else if (!/^(\d|[1-9]\d|100)(\.\d{1,2})?$/.test(value) || value > 100) {
                    callback(new Error('分成格式错误'));
                  } else {
                    callback();
                  }
            };
            return {
                dialogRemarksFormVisible: false,
                dialogDividedIntoSettingsFormVisible: false,
                formRemarks: {
                    remark: ''
                },
                formDividedIntoSettings: {
                    divide: ''
                },
                divideRules: {
                    divide: [
                        {required: true, validator: validateDivided, trigger: 'blur'}
                    ]
                },
                detailOption:{
                    id:''
                },
                bntLoading: false,
            }
        },
        computed: {

        },
        methods: {
            merchants(){
                let _self = this;
                console.log(_self.scope)
                router.push({
                    path: '/bizers/BizerMerchants',
                    query: {
                        bizer_id: _self.scope.row.bizer_id
                    }
                })
            },
            changeStatus(){
                let _self = this;
                api.get('/operBizer/changeStatus', {id: _self.scope.row.id}).then((data) => {
                    _self.scope.row.sign_status = data.sign_status;
                    _self.$message({
                        message: data.sign_status == 1 ? '解冻成功' : '冻结成功',
                        type: 'success'
                    });
                    _self.$emit('change', this.scope.$index, data)
                }).catch(() => {
                    _self.$message.warning('操作失败');
                }).finally(() => {
                    
                })
            },
            remarks() {
                this.dialogRemarksFormVisible = true;
            },
            submitRemarksForm(){
                //提交备注
                let _self = this;
                if (_self.formRemarks.remark) {
                    //提交
                    api.get('/operBizer/changeDetail', {id: _self.scope.row.id, remark: _self.formRemarks.remark}).then((data) => {
                        _self.dialogRemarksFormVisible = false
                        _self.$emit('refresh');
                    }).catch(() => {
                        _self.$message({
                          message: '修改失败',
                          type: 'warning'
                        });
                    }).finally(() => {

                    })
                    
                }else{
                    _self.$message.warning('请填写备注');
                }
            },
            submitSettingsdivide(){
                //提交分成
                let _self = this;
                _self.$refs.formDivided.validate(valid => {
                    if (valid) {
                        _self.formDividedIntoSettings.id = _self.scope.row.id;
                        _self.formDividedIntoSettings.divide = parseFloat(_self.formDividedIntoSettings.divide);
                        let params = _self.formDividedIntoSettings;
                        api.get('/operBizer/changeDetail', params).then(data => {
                            _self.dialogDividedIntoSettingsFormVisible = _self.dialogRemarksFormVisible = false;
                            _self.$message({
                                message: '修改成功',
                                type: 'success'
                            });
                            _self.$emit('refresh');
                        }).catch((error) => {
                            _self.$message({
                              message: error.response && error.response.message ? error.response.message:'请求失败',
                              type: 'warning'
                            });
                        }).finally(() => {
                            _self.bntLoading = false;
                        })
                    }
                })
            },
            dividedIntoSettings() {
                this.dialogDividedIntoSettingsFormVisible = true;
            },
        },
        components: {
        }
    }
</script>

<style scoped>

</style>