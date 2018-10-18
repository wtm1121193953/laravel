<template>
    <page title="业务员申请" v-loading="isLoading">
        <el-tabs type="card" v-model="activeName">
            <el-tab-pane :label="'新申请'+total" name="newRecords">
                <el-col>
                    <el-table :data="list" stripe>
                        <el-table-column prop="created_at" label="申请签约时间"/>
                        <el-table-column prop="name" label="昵称">
                            <template slot-scope="scope">
                                <span> {{ scope.row.bizerInfo.name }} </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="mobile" label="手机号">
                            <template slot-scope="scope">
                                <span> {{ scope.row.bizerInfo.mobile }} </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="remark" label="备注"></el-table-column>

                        <div>
                            <el-table-column fixed="right" label="操作">
                                <template slot-scope="scope">
                                    <el-button type="text" @click="signing(scope.$index)">签约</el-button>
                                    <el-button type="text" @click="refusal(scope.$index)">拒绝</el-button>
                                </template>
                            </el-table-column>
                        </div>
                    </el-table>

                    <el-pagination
                            class="fr m-t-20"
                            layout="total, prev, pager, next"
                            :current-page.sync="query.page"
                            @current-change="getNewList"
                            :page-size="15"
                            :total="total"/>
                </el-col>
            </el-tab-pane>
            <el-tab-pane label="已拒绝" name="rejectRecords">
                <el-col>
                    <el-table :data="rejectList" stripe>
                        <el-table-column prop="apply_time" label="申请签约时间"/>
                        <el-table-column prop="name" label="昵称">
                            <template slot-scope="scope">
                                <span> {{ scope.row.bizerInfo.name }} </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="mobile" label="手机号">
                            <template slot-scope="scope">
                                <span> {{ scope.row.bizerInfo.mobile }} </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="拒绝签约时间"></el-table-column>
                        <el-table-column prop="note" label="原因"></el-table-column>
                        <el-table-column prop="remark" label="备注"></el-table-column>
                    </el-table>

                    <el-pagination
                            class="fr m-t-20"
                            layout="total, prev, pager, next"
                            :current-page.sync="rejectPage"
                            @current-change="getRejectList"
                            :page-size="15"
                            :total="rejectTotal"/>
                </el-col>
            </el-tab-pane>
        </el-tabs>


        <el-dialog title="签约业务员" :visible.sync="dialogSigningFormVisible" width="30%">
            <el-form :model="formSigning" ref="formSigning" :rules="rules" label-width="70px">
                <el-form-item>
                    确定签约业务员<span class="c-danger">{{detailOption.bizerInfo.name}}</span>
                </el-form-item>
                <el-form-item label="分成" prop="divide">
                    <el-input v-model="formSigning.divide" auto-complete="off" style="width:90%;"/> %
                </el-form-item>
                <el-form-item label="原因" prop="note">
                    <el-input type="textarea" v-model="formSigning.note" auto-complete="off" placeholder="最多50个字" style="width:90%;"/>
                </el-form-item>
                <el-form-item label="备注">
                    {{detailOption.remark}}
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogSigningFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="signingSubmit(1)" v-loading="bntLoading" :disabled="bntLoading">提 交</el-button>
            </div>
        </el-dialog>

        <el-dialog title="拒绝业务员" :visible.sync="dialogRefusalFormVisible" width="30%">
            <el-form :model="formRefusal" ref="formRefusal" :rules="formRefusalRules" label-width="70px">
                <el-form-item>
                    确定拒绝签约业务员<span class="c-danger">{{detailOption.bizerInfo.name}}</span>
                </el-form-item>
                <el-form-item prop="note" label="原因">
                    <el-input type="textarea" v-model="formRefusal.note" auto-complete="off" placeholder="最多50个字"/>
                </el-form-item>
                <el-form-item label="备注">
                    {{detailOption.remark}}
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogRefusalFormVisible = false">取 消</el-button>
                <el-button type="primary"  v-loading="bntLoading" :disabled="bntLoading" @click="signingSubmit(-1)">提 交</el-button>
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
                activeName: 'newRecords',
                secondTable: false,
                query: {
                    page: 1
                },
                list: [],
                total: 0,

                dialogSigningFormVisible: false,
                dialogRefusalFormVisible: false,
                formSigning: {
                    status: 1,
                    divide: '',
                    note: ''
                },
                formRefusal: {
                    status: -1,
                    note: ''
                },
                rules: {
                    divide: [
                        {required: true, validator: validateDivided, trigger: 'blur'}
                    ],
                    note: [
                        {max: 50, message: '原因不能超过50个字'},
                    ]
                },
                formRefusalRules: {
                    note: [
                        {required: true, message: '原因不能为空'},
                        {max: 50, message: '原因不能超过50个字'},
                    ]
                },
                bntLoading: false,
                detailOption:{
                    bizerInfo:{
                        name:'',
                    },
                    id:'',
                    remark: '',
                },

                rejectList: [],
                rejectPage: 1,
                rejectTotal: 0,
            }
        },
        computed: {

        },
        methods: {
            getListData(){
                this.getNewList();
                this.getRejectList();
            },
            getNewList() {
                this.isLoading = true;
                let params = {};
                this.query.status = 0;
                Object.assign(params, this.query);
                api.get('/bizerRecords', params).then(data => {
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            signing(index) {
                this.dialogSigningFormVisible = true;
                // alert(index)
                this.detailOption = this.list[index]
            },
            refusal(index) {
                this.dialogRefusalFormVisible = true;
                this.detailOption = this.list[index];
            },
            signingSubmit(status){
                let _self = this;
                let params,successMsg;
                if(status && status == 1){
                    let isValid;
                    
                    _self.$refs.formSigning.validate(valid => {
                        if (valid) {
                            _self.formSigning.id = this.detailOption.id;
                            _self.formSigning.divide = parseFloat(_self.formSigning.divide);
                            params = _self.formSigning;
                            isValid = true;
                        }else{
                            isValid = false;
                        }
                    });
                    successMsg = "签约成功";
                    if(!isValid)return false;
                }else{
                    let flag = false;
                    this.$refs.formRefusal.validate(valid => {
                        if (valid) {
                            _self.formRefusal.id = _self.detailOption.id;
                            params = _self.formRefusal;
                            successMsg = "已拒绝";
                        } else {
                            flag = true;
                        }
                    });
                    if (flag) return;
                }

                _self.bntLoading = true;
                api.get('/bizerRecord/contractBizer', params).then(data => {
                    _self.dialogSigningFormVisible = _self.dialogRefusalFormVisible = false;
                    _self.$message({
                        message: successMsg,
                        type: 'success'
                    });
                    _self.getListData();
                }).catch((error) => {
                    _self.$message({
                      message: error.response && error.response.message ? error.response.message:'请求失败',
                      type: 'warning'
                    });
                }).finally(() => {
                    _self.bntLoading = false;
                })
                    
            },
            getRejectList() {
                api.get('/bizerRecord/getRejectList', {page: this.rejectPage}).then(data => {
                    this.rejectList = data.list;
                    this.rejectTotal = data.total;
                })
            },
        },
        created(){
            this.getListData();
        },
        components: {

        }
    }
</script>

<style scoped>

</style>
