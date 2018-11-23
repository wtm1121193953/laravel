<template>
    <page :title=" isAudit ? '审核商户': '超市商户列表'"     v-loading="isLoading" >
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户ID" >
                    <el-input v-model="query.merchantId" size="small"  placeholder="商户ID"  class="w-100" clearable></el-input>
                </el-form-item>
                <el-form-item prop="name" label="商户名称" >
                    <el-input v-model="query.name" size="small"  placeholder="商户名称" clearable @keyup.enter.native="search"/>
                </el-form-item>
                <el-form-item prop="signboardName" label="商户招牌名" >
                    <el-input v-model="query.signboardName" size="small"  placeholder="商户招牌名" clearable @keyup.enter.native="search"/>
                </el-form-item>
                <el-form-item prop="startDate" label="添加商户开始时间">
                    <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        size="small"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() > new Date(query.endDate) - 8.64e7}}"
                    ></el-date-picker>
                </el-form-item>
                <el-form-item prop="startDate" label="结束时间">
                    <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        size="small"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                    ></el-date-picker>
                </el-form-item>
                <el-form-item label="审核状态" prop="auditStatus"  v-if="isAudit">
                    <el-select v-model="query.auditStatus" size="small" multiple  placeholder="请选择" class="w-250">
                        <el-option label="待审核" value="0" />
                        <el-option label="重新提交审核" value="3"/>
                    </el-select>
                </el-form-item>
                <el-form-item label="审核状态" prop="auditStatus"  v-else>
                    <el-select v-model="query.auditStatus" size="small"  multiple placeholder="请选择" class="w-150">
                        <el-option label="待审核" value="0"/>
                        <el-option label="审核通过" value="1"/>
                        <el-option label="审核不通过" value="2"/>
                        <el-option label="重新提交审核" value="3"/>
                    </el-select>
                </el-form-item>
                <el-form-item label="结算周期" prop="settlementCycleType">
                    <el-select v-model="query.settlementCycleType" size="small" multiple placeholder="请选择" class="w-150">
                        <el-option label="周结" value="1"/>
                        <el-option label="T+1(自动)" value="3"/>
                        <el-option label="T+1(人工)" value="6"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="operId" label="激活运营中心ID"  >
                    <el-input v-model="query.operId" size="small"   placeholder="激活运营中心ID"  class="w-100" clearable />
                </el-form-item>
                <el-form-item prop="operName" label="激活运营中心名称"  >
                    <el-input v-model="query.operName" size="small"  placeholder="激活运营中心名称"  clearable></el-input>
                </el-form-item>
                <el-form-item label="商户状态" prop="status">
                    <el-select v-model="query.status" size="small" class="w-150">
                        <el-option label="全部" value=""/>
                        <el-option label="正常" value="1"/>
                        <el-option label="冻结" value="2"/>
                        <el-option label="未知" value="3"/>
                    </el-select>
                </el-form-item>
                <!--<el-form-item prop="memberNameOrMobile" label="我的员工"  >
                    <el-input v-model="query.memberNameOrMobile" size="small"  placeholder="请输入员工姓名或手机号码搜索"  clearable></el-input>
                </el-form-item>
                <el-form-item prop="bizerNameOrMobile" label="业务员"  >
                    <el-input v-model="query.bizerNameOrMobile" size="small"  placeholder="请输入业务员昵称或手机号码搜索"  clearable></el-input>
                </el-form-item>-->
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
                </el-form-item>

             </el-form>
            </el-col>
            <el-table :data="list" v-loading="tableLoading" stripe>
                <el-table-column prop="created_at" label="添加时间"  width="160px" />
                <el-table-column prop="id" size="mini"	 label="商户ID"/>
                <el-table-column prop="name" label="商户名称"/>
                <el-table-column prop="name" label="商户类型">
                    <template slot-scope="scope">
                        <span>超市类</span>
                    </template>
                </el-table-column>
                <el-table-column prop="signboard_name" label="商户招牌名"/>
                <el-table-column prop="operId" size="mini" label="激活运营中心ID"/>
                <el-table-column prop="operName" label="激活运营中心名称"/>
                <!--<el-table-column prop="creatorOperId"  size="mini" label="录入运营中心ID"/>-->
                <!--<el-table-column prop="creatorOperName" label="录入运营中心名称"/>-->
                <!--<el-table-column label="签约人">
                    <template slot-scope="scope">
                        <span v-if="scope.row.bizer"><span class="c-green">业务员 </span>{{scope.row.bizer.name}}/{{scope.row.bizer.mobile}}</span>
                        <span v-else-if="scope.row.operBizMember"><span class="c-light-gray">员工 </span>{{scope.row.operBizMember.name}}/{{scope.row.operBizMember.mobile}}</span>
                        <span v-else>无</span>
                    </template>
                </el-table-column>
                <el-table-column prop="categoryPath" label="行业">
                    <template slot-scope="scope">
                <span v-for="item in scope.row.categoryPath" :key="item.id">
                    {{ item.name }}
                </span>
                    </template>
                </el-table-column>-->
                <el-table-column prop="city" label="城市">
                    <template slot-scope="scope">
                        <!--<span> {{ scope.row.province }} </span>-->
                        <span> {{ scope.row.city }} </span>
                        <span> {{ scope.row.area }} </span>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="商户状态">
                    <template slot-scope="scope" v-if="scope.row.audit_status == 1 || scope.row.audit_status == 3">
                        <span v-if="scope.row.status === 1" class="c-green">正常</span>
                        <span v-else-if="scope.row.status === 2" class="c-danger">已冻结</span>
                        <span v-else>未知 ({{scope.row.status}})</span>
                    </template>
                </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.audit_status === 0" class="c-warning">待审核</span>
                        <el-popover
                                v-else-if="scope.row.audit_status === 1"
                                placement="bottom-start"
                                width="200px"  trigger="hover"
                                @show="showMessage(scope)"
                            :disabled="scope.row.audit_suggestion == ''">
                            <div   slot="reference" class="c-green"><p>审核通过</p><span class="message">{{scope.row.audit_suggestion}}</span></div>
                            <unaudit-record-reason    :data="auditRecord"  />
                        </el-popover>
                          <el-popover
                              v-else-if="scope.row.audit_status === 2"
                              placement="bottom-start"
                              width="200px"  trigger="hover"
                              @show="showMessage(scope)"
                              :disabled="scope.row.audit_suggestion == ''" >
                              <div   slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span></div>
                                <unaudit-record-reason    :data="auditRecord"  />
                          </el-popover>
                    <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
                </el-table-column>
                <el-table-column prop="settlement_cycle_type" label="结算周期">
                    <template slot-scope="scope">
                        <span>{{ {1: '周结', 2: '半月结', 3: 'T+1(自动)', 4: '半年结', 5: '年结', 6: 'T+1(人工)', 7: '未知',}[scope.row.settlement_cycle_type] }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope)">查看</el-button>
                    <el-button type="text" @click="edit(scope)">编辑</el-button>
                    <el-button v-if="parseInt(scope.row.audit_status)!==0 && parseInt(scope.row.audit_status)!==2" type="text" @click="changeStatus(scope.row)">{{parseInt(scope.row.status) === 1 ? '冻结' : '解冻'}}</el-button>
                    <template v-if="scope.row.audit_status === 0 || scope.row.audit_status === 3">
                        <el-button type="text" @click="detail(scope,3)">审核</el-button>
                        <el-dropdown trigger="click" @command="(command) => {audit(scope, command)}">
                            <el-button type="text">
                              快捷审核 <i class="el-icon-arrow-down"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="1">审核通过</el-dropdown-item>
                                <el-dropdown-item command="2">审核不通过</el-dropdown-item>
                                <!--<el-dropdown-item  v-if="scope.row.oper_id == 0"  command="3">打回到商户池</el-dropdown-item>-->
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
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
        <el-dialog :visible.sync="showDetail" width="70%" title="商户详情">
            <merchant-detail :data="currentMerchant" @change="() => {getList(); showDetail = false;}"/>
        </el-dialog>
        <el-dialog title="审核意见" :visible.sync="unAudit" :close-on-click-modal="false">
            <unaudit-message   @cancel="unAudit = false"  :data="detailMerchant"   @change="merchantChange"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import CsMerchantDetail from './merchant-detail'
    import UnauditMessage from './unaudit-message'
    import UnauditRecordReason from './unaudit-record-reason'

    export default {
        name: "cs-merchant-list",
        data(){
            return {
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                unAudit:false,
                detailMerchant:null,
                query: {
                    name: '',
                    signboardName:'',
                    auditStatus: [],
                    status:'',
                    page: 1,
                    merchantId: '',
                    startDate: '',
                    endDate: '',
                    operName:'',
                    operId:'',
                    settlementCycleType:'',
                    creatorOperName:'',
                    creatorOperId:'',
                    memberNameOrMobile: '',
                    bizerNameOrMobile: '',
                },
                list: [],
                auditRecord:[],
                total: 0,
                currentMerchant: null,
                tableLoading: false,
            }
        },
        computed: {
            isAudit(){
                let isAudit = this.$route.path;
                return isAudit=="/merchant/unaudits"
            }
        },
        methods: {
            merchantChange(){
                this.getList();
            },
            showMessage(scope){
                 api.get('/merchant/audit/record/newest', {id: scope.row.id}).then(data => {
                        this.auditRecord = [data];
                    })
            },
            search() {
                if (this.query.startDate > this.query.endDate) {
                    this.$message.error('搜索的开始时间不能大于结束时间！');
                    return false;
                }
                //待审核页面bug修复
                if( this.query.auditStatus .length == 0  && this.isAudit ){
                    this.query.auditStatus = ['0', '3']
                }
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/cs/merchants', params).then(data => {
                    console.log(data.list);
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            detail(scope){
                let self = this;
                router.push({
                    path: 'cs/merchant/detail',
                    name: 'CsMerchantDetail',
                    query: {
                        id: scope.row.id,
                    },
                    params: self.query,
                })
            },

            edit(scope){
                router.push({
                    path: '/cs/merchant/edit',
                    query: {id: scope.row.id},
                })
                return false;
            },
            //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
            audit(scope, type){
                if(type==2 ||type==1){
                    api.get('cs/merchant/detail', {id: scope.row.id}).then(data => {
                        this.detailMerchant = data;
                        this.detailMerchant.type = type;
                        this.unAudit = true;
                    });
                }else{
                    let message = ['', '审核通过', '审核不通过'/*, '打回到商户池'*/][type];
                    this.$confirm(`确定 ${message} 吗?`, scope.row.name).then(() => {
                        api.post('/merchant/audit', {id: scope.row.id, type: type}).then(data => {
                            this.$alert(message + ' 操作成功');
                            this.getList();
                        })
                    });
                }

            },
            downloadExcel() {
                let message = '确定要导出当前筛选的商户列表么？';
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                let day = 0;
                if (this.query.startDate && this.query.endDate) {
                    day = (new Date(this.query.endDate) - new Date(this.query.startDate)) / 24 / 3600 / 1000;
                }
                if (day > 31) {
                    this.$message.warning('您导出的数据量太大，请按月导出');
                    return;
                }

                this.$confirm(message).then(() => {
                    let data = this.query;
                    let params = [];
                    Object.keys(data).forEach((key) => {
                        let value =  data[key];
                        if (typeof value === 'undefined' || value == null) {
                            value = '';
                        }
                        params.push([key, encodeURIComponent(value)].join('='))
                    }) ;
                    let uri = params.join('&');

                    location.href = `/api/admin/cs/merchant/export?${uri}`;
                })
            },
            changeStatus(row){
                let status = row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/cs/merchant/changeStatus', {id: row.id, status: status}).then((data) => {
                    row.status = status;
                    this.$emit('change', this.scope, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
        },
        created(){
            if(this.isAudit){
                this.query.auditStatus=['0', '3'];
                Object.assign(this.query, this.$route.params);
                this.getList();
            }else{
                Object.assign(this.query, this.$route.params);
                this.getList();
            }

        },
        components: {
            CsMerchantDetail,
            UnauditMessage,
            UnauditRecordReason,
        }
    }
</script>

<style scoped>
    .message{
        overflow: hidden;
        text-overflow:ellipsis;
        white-space: nowrap;
        width:120px;
        font-size:12px;
        color:gray;
    }

</style>