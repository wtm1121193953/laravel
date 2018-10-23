<template>
    <page title="运营中心列表" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="createdAt" label="添加时间">
                <el-date-picker
                        v-model="query.createdAt"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
            </el-form-item>
            <el-form-item prop="name" label="运营中心名称">
                <el-input v-model="query.name" placeholder="请输入运营中心名称" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="contacter" label="负责人">
                <el-input v-model="query.contacter" placeholder="请输入负责人" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="tel" label="联系电话">
                <el-input v-model="query.tel" placeholder="请输入联系电话" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="cityId" label="所在城市">
                <el-cascader 
                    change-on-select
                    clearable
                    filterable
                    :options="cityOptions"
                    :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                    v-model="query.cityId">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="status" label="状态">
                <el-select v-model="query.status" multiple class="w-100">
                    <el-option label="正常" :value="1"/>
                    <el-option label="拒绝" :value="-1"/>
                    <el-option label="申请中" :value="0"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
            <el-button class="fr" type="success" size="small" icon="el-icon-plus" @click="add">申请运营中心</el-button>
        </el-form>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="operInfo.name" label="运营中心名称"/>
            <el-table-column prop="operInfo.contacter" label="负责人"/>
            <el-table-column prop="operInfo.tel" label="联系电话"/>
            <el-table-column prop="operInfo" label="所在城市">
                <template slot-scope="scope">
                    <!-- <span> {{ scope.row.province }} </span> -->
                    <span> {{ scope.row.operInfo.province }} </span>
                    <span> {{ scope.row.operInfo.city }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="divide" label="我的分成">
                <template slot-scope="scope">
                    <span>{{scope.row.divide}}%</span>
                </template>
            </el-table-column>
            <el-table-column prop="sign_status" label="合作状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.sign_status == 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.sign_status == 0" class="c-warning">冻结</span>
                    <span v-else class="c-danger">未知（{{scope.row.sign_status}}）</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.status === 1" @click="toMerchants(scope.row.oper_id)" type="text">查看商户</el-button>
                    <!-- <el-button type="text" @click="contract">查看合同</el-button> -->
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

        <el-dialog title="添加运营中心" :visible.sync="dialogFormVisible" width="30%">
            <el-form label-width="120px" ref="addRegionData" :model="addRegionData" :rules="addRegionDataRules">
                <el-form-item label="昵称">
                    {{ user.name }}
                </el-form-item>
                <el-form-item label="电话">
                    {{ user.mobile }}
                </el-form-item>
                <el-form-item prop="oper_id" label="运营中心名称">
                    <el-select
                        filterable
                        remote
                        :remote-method="getOperNameList"
                        v-model="addRegionData.oper_id"
                        :loading="selectLoading"
                        placeholder="请输入运营中心名称"
                        style="width:100%;">
                        <el-option v-for="item in regionOptions" :label="item.name"  :key="item.id" :value="item.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="remark" label="备注">
                    <el-input type="textarea" v-model="addRegionData.remark" auto-complete="off"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="addRegion" v-loading="regionLoading">发送申请</el-button>
            </div>
        </el-dialog>

        <el-dialog title="合同" :visible.sync="dialogContractVisible">
            合同内容
        </el-dialog>

        <el-dialog title="提示" :visible.sync="dialogPromptVisible" width="30%" @closed="closeDialogPrompt">
            <el-form label-width="110px">
                <el-form-item label="运营中心名称：">
                    {{dialogTips[dialogTipsCurrent].operName}}
                </el-form-item>
                <el-form-item label="不通过原因：">
                    <div class="prompt-txt">{{dialogTips[dialogTipsCurrent].note}}</div>
                </el-form-item>
            </el-form>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import {mapState} from 'vuex'

    export default {
        data(){
            return {
                isLoading: false,
                cityOptions: [],
                regionOptions:[],
                query: {
                    createdAt: '',
                    name: '',
                    contacter: '',
                    tel: '',
                    // provinceId:'',//省份ID
                    cityId:[],//城市ID不能写,组件是自动生成的
                    status: [0, 1],
                    page: 1
                },
                list: [],
                total: 0,
                dialogFormVisible: false,
                dialogContractVisible: false,
                dialogPromptVisible: false,
                addRegionData: {
                    oper_id: '',
                    remark: ''
                },
                regionLoading: false,
                dialogTips:[
                    {
                        operName:'',
                        note:''
                    }
                ],
                dialogTipsCurrent: 0,
                selectLoading: false,

                addRegionDataRules: {
                    oper_id: [
                        {required: true, message: '运营中心名称不能为空'},
                    ],
                    remark: [
                        {max: 50, message: '备注不能超过50个字'},
                    ]
                }
            }
        },
        computed: {
            ...mapState([
                'user',
            ]),
        },
        methods: {
            search(){
                let _self = this;
                _self.query.page = 1;
                _self.getList();
            },
            getList(){
                let _self = this;
                _self.isLoading = true;
                let params = {};
                if (_self.query.createdAt && _self.query.createdAt.length > 0 ) {
                    params.startTime = _self.query.createdAt[0];
                    params.endTime = _self.query.createdAt[1];
                }else{
                    params.startTime = '';
                    params.endTime = '';
                }
                Object.assign(params, _self.query);
                api.get('/api/bizer/opers', params).then(data => {
                    _self.query.page = params.page;
                    _self.list = data.list;
                    _self.total = data.total;
                    if (data.tips && data.tips.length > 0) {
                        _self.dialogTips = data.tips;
                        _self.dialogPromptVisible = true;
                    }
                }).catch(() =>{
                    _self.$message({
                      message: '请求失败',
                      type: 'warning'
                    });
                }).finally(() => {
                    _self.isLoading = false;
                })
            },
            add(){
                this.dialogFormVisible = true;
            },
            contract(){
                this.dialogContractVisible = true;
            },
            prompt(){
                this.dialogPromptVisible = true;
            },
            addRegion(){
                this.$refs.addRegionData.validate(valid => {
                    if (valid) {
                        let _self = this;
                        if (!_self.addRegionData.oper_id) {
                            _self.$message({
                                message: '请选择运营中心',
                                type: 'warning'
                            });
                            return;
                        }
                        _self.regionLoading = true;
                        api.post('/api/bizer/oper/add', _self.addRegionData).then(data => {
                            _self.regionLoading = false;
                            _self.dialogFormVisible = false;
                            _self.$message({
                                message: '添加成功',
                                type: 'success'
                            });
                            this.getList();
                        }).finally(() => {
                            _self.regionLoading = false;
                        })
                    }
                });
            },
            toMerchants(oper_id){
                //改变vuex状态
                store.commit('setCurrentMenu', '/merchants');
                router.push({ path: '/merchants', query: { operId: oper_id }})
            },
            closeDialogPrompt(){
                //关闭弹窗事件
                let _self = this;
                if (_self.dialogTipsCurrent < _self.dialogTips.length - 1) {
                    _self.dialogTipsCurrent ++;
                    _self.dialogPromptVisible = true;
                }
            },
            getOperNameList(query) {
                if (query !== '') {
                    this.selectLoading = true;
                    api.get('/api/bizer/oper/name_list', {operName: query}).then(data => {
                        this.regionOptions = data.list;
                        this.selectLoading = false;
                    });
                } else {
                    this.regionOptions = [];
                }
            }
        },
        created(){
            let _self = this;
            if (_self.$route.params){
                Object.assign(_self.query, _self.$route.params);
            }
            api.get('/api/bizer/area/tree?tier=2').then(data => {
                _self.cityOptions = data.list;
            });
            _self.getList();
        },
        components: {

        }
    }
</script>

<style scoped>
.prompt-txt {
    line-height: 24px;
    padding: 8px 0;
}
</style>
