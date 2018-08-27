<template>
    <page title="业务员申请" v-loading="isLoading">
        <el-tabs type="card" v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="新申请" name="first"></el-tab-pane>
            <el-tab-pane label="已拒绝" name="second"></el-tab-pane>
        </el-tabs>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="申请签约时间"/>
            <el-table-column prop="name" label="姓名">
                <template slot-scope="scope">
                    <span> {{ scope.row.bizerInfo.name }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="mobile" label="手机号">
                <template slot-scope="scope">
                    <span> {{ scope.row.bizerInfo.mobile }} </span>
                </template>
            </el-table-column>
            
            <template v-if="secondTable">
                <el-table-column prop="updated_at" label="拒绝签约时间"/>
                <el-table-column prop="remark" label="原因"/>
            </template>
            
            <div v-if="!secondTable">
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
                @current-change="getList"
                :page-size="15"
                :total="total"/>

        <el-dialog title="签约业务员" :visible.sync="dialogSigningFormVisible" width="30%">
            <el-form :model="formSigning" ref="formSigning" :rules="rules" label-width="70px">
                <el-form-item>
                    确定签约业务员<span class="c-danger">{{detailOption.bizerInfo.name}}</span>
                </el-form-item>
                <el-form-item label="分成" prop="divide">
                    <el-input v-model="formSigning.divide" auto-complete="off" style="width:90%;"/> %
                </el-form-item>
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="formSigning.remark" auto-complete="off" placeholder="最多50个字" style="width:90%;"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogSigningFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="signingSubmit(1)" v-loading="bntLoading" :disabled="bntLoading">提 交</el-button>
            </div>
        </el-dialog>

        <el-dialog title="拒绝业务员" :visible.sync="dialogRefusalFormVisible" width="30%">
            <el-form :model="formRefusal" label-width="70px">
                <el-form-item>
                    确定拒绝签约业务员<span class="c-danger">{{detailOption.bizerInfo.name}}</span>
                </el-form-item>
                <el-form-item label="原因">
                    <el-input type="textarea" v-model="formRefusal.remark" auto-complete="off" placeholder="最多50个字"/>
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
                activeName: 'first',
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
                    remark: ''
                },
                formRefusal: {
                    status: -1,
                    remark: ''
                },
                rules: {
                    divide: [
                        {validator: validateDivided, trigger: 'blur'}
                    ]
                },
                bntLoading: false,
                detailOption:{
                    bizerInfo:{
                        name:'',
                    },
                    id:'',
                },
            }
        },
        computed: {

        },
        methods: {
            getList() {
                this.isLoading = true;
                let params = {};
                this.query.selectStatus = this.activeName;
                Object.assign(params, this.query);
                api.get('/bizerRecord', params).then(data => {
                    console.log(data.list)
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            handleClick(tab, event) {
                // console.log(tab, event);
                let _self = this;
                this.getList();
                if ( _self.activeName == "first" ) {
                    _self.secondTable = false;
                } else if ( _self.activeName == "second" ) {
                    _self.secondTable = true;
                }
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
                let params;
                if(status && status == 1){
                    let isValid;
                    _self.$refs.formSigning.validate(valid => {
                        if (valid) {
                            _self.formSigning.id = this.detailOption.id;
                            _self.formSigning.divide = parseInt(_self.formSigning.divide);
                            params = _self.formSigning;
                            isValid = true;
                        }else{
                            isValid = false;
                        }
                    })
                    if(!isValid)return false;
                }else{
                    _self.formRefusal.id = _self.detailOption.id;
                    params = _self.formRefusal;
                }
                _self.bntLoading = true;
                api.get('/bizerRecord/contractBizer', params).then(data => {
                    _self.dialogSigningFormVisible = _self.dialogRefusalFormVisible = false;
                    _self.$message({
                        message: '签约成功',
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
                    
            },
            // refusalSubmit(){
            //     let _self = this;
            //     _self.bntLoading = true;
            //     _self.formRefusal.id = this.detailOption.id;
            //     api.get('/bizerRecord/contractBizer', _self.formRefusal).then(data => {
            //        _self.dialogRefusalFormVisible = false;
            //     }).catch((error) => {
            //         _self.$message({
            //           message: error.response && error.response.message ? error.response.message:'请求失败',
            //           type: 'warning'
            //         });
            //     }).finally(() => {
            //         _self.bntLoading = false;
            //     })
            // },
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
