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
                <el-select v-model="query.status" class="w-100">
                    <el-option label="全部" value=""/>
                    <el-option label="正常" value="1"/>
                    <el-option label="拒绝" value="-1"/>
                    <el-option label="申请中" value="0"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-button class="fr" type="primary" icon="el-icon-plus" @click="add">添加运营中心</el-button>

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
                    <span>{{scope.row.divide * 100}}%</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="合作状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 0" class="c-warning">申请中</span>
                    <span v-else-if="scope.row.status === -1" class="c-danger">拒绝</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button @click="toMerchants(scope.row.id)" type="text">查看商户</el-button>
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
            <el-form label-width="100px">
                <el-form-item label="电话">
                    {{ username }}
                </el-form-item>
                <el-form-item label="运营中心名称">
                    <el-select v-model="addRegionData.oper_id" placeholder="请选择运营中心" style="width:100%;">
                        <el-option v-for="item in regionOptions" :label="item.name"  :key="item.id" :value="item.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item label="备注">
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
                    provinceId:'',//省份ID
                    //cityId:'',//城市ID不能写,组件是自动生成的
                    status: '',
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
            }
        },
        computed: {
            ...mapState([
                'user',
            ]),
            username(){
                return this.user ? (this.user.operName || this.user.account || this.user.mobile) : '';
            }
        },
        methods: {
            search(){
                var _self = this;
                this.query.page = 1;
                this.getList();
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
                    console.log(data)
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
                }).catch(() => {
                    _self.$message({
                      message: '添加失败',
                      type: 'warning'
                    });
                }).finally(() => {
                    _self.regionLoading = false;
                })
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
                
            }
        },
        created(){
            let _self = this;
            if (_self.$route.params){
                Object.assign(_self.query, _self.$route.params);
            }
            api.get('/api/bizer/area/tree?tier=2').then(data => {
                // console.log(data.list)
                _self.cityOptions = data.list;
            });
            api.get('/api/bizer/oper/name_list').then(data => {
                // console.log(data.list)
                _self.regionOptions = data.list;
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
