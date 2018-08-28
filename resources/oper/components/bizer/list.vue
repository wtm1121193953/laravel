<template>
    <page title="我的业务员" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="加入时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 16)}}
                </template>
            </el-table-column>
            <el-table-column prop="name" label="姓名"/>
            <el-table-column prop="mobile" label="手机号"/>
            <el-table-column prop="dividedInto" label="业务员分成"/>
            <el-table-column prop="activeMerchantNumber" label="发展商户（家）"/>
            <el-table-column prop="auditMerchantNumber" label="审核通过商户（家）"/>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="210px">
                <template slot-scope="scope">
                    <el-button type="text" @click="remarks">备注</el-button>
                    <el-button type="text" @click="merchants">业务</el-button>
                    <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '冻结' : '解冻'}}</el-button>
                    <el-button type="text" @click="dividedIntoSettings">分成设置</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"/>

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
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
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
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
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
                        {validator: validateDivided, trigger: 'blur'}
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
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                let _self = this;
                api.get('/operBizMembers', this.query).then(data => {
                    _self.list = data.list;
                    _self.total = data.total;
                }).catch((error) => {
                    _self.$message({
                      message: error.response && error.response.message ? error.response.message:'请求失败',
                      type: 'warning'
                    });
                }).finally(() => {

                })
            },
            merchants(){
                // router.push({
                //     path: '/operBizMember/merchants',
                //     query: {
                //         id: this.scope.row.id
                //     }
                // })
            },
            changeStatus(){
                // let status = this.scope.row.status === 1 ? 2 : 1;
                // this.$emit('before-request')
                // api.post('/operBizMember/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                //     this.scope.row.status = status;
                //     this.$emit('change', this.scope.$index, data)
                // }).finally(() => {
                //     this.$emit('after-request')
                // })
            },
            remarks() {
                this.dialogRemarksFormVisible = true;
            },
            submitRemarksForm(){
                //提交备注
                let _self = this;
                if (!_self.formRemarks.remark) {
                    //提交
                }else{
                    _self.$message({
                          message: '请填写备注',
                          type: 'warning'
                    });
                }
            },
            submitSettingsdivide(){
                //提交分成
                let _self = this;
                _self.$refs.formDivided.validate(valid => {
                    if (valid) {
                        _self.formDividedIntoSettings.id = this.detailOption.id;
                        _self.formDividedIntoSettings.divide = parseInt(_self.formDividedIntoSettings.divide);
                        let params = _self.formDividedIntoSettings;
                        api.get('', params).then(data => {
                            _self.dialogDividedIntoSettingsFormVisible = _self.dialogRemarksFormVisible = false;
                            _self.$message({
                                message: '修改成功',
                                type: 'success'
                            });
                            _self.getList();
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
        created(){
            this.getList();
        },
        components: {

        }
    }
</script>

<style scoped>

</style>