<template>
    <page title="商户审核管理" v-loading="isLoading" >

                <el-col>
                    <el-form v-model="query" inline>
                        <el-form-item prop="merchantId" label="商户ID">
                            <el-input v-model="query.merchantId" size="small" class="w-100" clearable></el-input>
                        </el-form-item>
                        <el-form-item prop="name" label="商户名称" >
                            <el-input v-model="query.name" size="small" placeholder="商户名称" clearable @keyup.enter.native="search"/>
                        </el-form-item>
                        <el-form-item prop="signBoardName" label="商户招牌名" >
                            <el-input v-model="query.signBoardName" size="small" placeholder="商家招牌名" clearable @keyup.enter.native="search"/>
                        </el-form-item>
                        <el-form-item prop="startDate" label="添加商户开始时间">
                            <el-date-picker
                                v-model="query.startDate"
                                type="date"
                                size="small"
                                placeholder="选择开始日期"
                                format="yyyy 年 MM 月 dd 日"
                                value-format="yyyy-MM-dd"
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
                        <el-form-item prop="operId" label="激活运营中心ID">
                            <el-input v-model="query.operId" size="small"   class="w-100" clearable />
                        </el-form-item>
                        <el-form-item prop="operName" label="激活运营中心名称">
                            <el-input v-model="query.operName" size="small"  clearable></el-input>
                        </el-form-item>
                        <el-form-item prop="creatorOperId" label="录入运营中心ID">
                            <el-input v-model="query.creatorOperId" size="small"  class="w-100" clearable />
                        </el-form-item>
                        <el-form-item prop="creatorOperName" label="录入运营中心名称">
                            <el-input v-model="query.creatorOperName" size="small" class="w-150"   clearable></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                        </el-form-item>
                        <el-form-item class="fr">
                            <el-button type="success" size="small" @click="downloadExcel">导出Excel</el-button>
                        </el-form-item>
             </el-form>
        </el-col>


                <el-table :data="list" v-loading="tableLoading" stripe>
                    <el-table-column prop="created_at" label="添加时间"/>
                    <el-table-column prop="id" size="mini"	 label="商户ID"/>
                    <el-table-column prop="name" label="商户名称"/>
                    <el-table-column prop="signboard_name" label="商户招牌名"/>
                    <el-table-column prop="operId" size="mini" label="激活运营中心ID"/>
                    <el-table-column prop="operName" label="激活运营中心名称"/>
                    <el-table-column prop="creatorOperId"  size="mini" label="录入运营中心ID"/>
                    <el-table-column prop="creatorOperName" label="录入运营中心名称"/>
                    <el-table-column prop="categoryPath" label="行业">
                        <template slot-scope="scope">
                    <span v-for="item in scope.row.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="city" label="城市">
                        <template slot-scope="scope">
                            <!--<span> {{ scope.row.province }} </span>-->
                            <span> {{ scope.row.city }} </span>
                            <span> {{ scope.row.area }} </span>
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
                            <span v-else-if="scope.row.audit_status === 3" class="c-warning">待审核(重新提交)</span>
                            <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
             </el-table-column>
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope)">查看</el-button>
                    <template v-if="scope.row.audit_status === 0 || scope.row.audit_status === 3">
                        <el-button type="text" @click="detail(scope,3)">审核</el-button>
                        <el-dropdown trigger="click" @command="(command) => {audit(scope, command)}">
                            <el-button type="text">
                              快捷审核 <i class="el-icon-arrow-down"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="1">审核通过</el-dropdown-item>
                                <el-dropdown-item command="2">审核不通过</el-dropdown-item>
                                <el-dropdown-item  v-if="scope.row.oper_id == 0"  command="3">打回到商户池</el-dropdown-item>
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
    import MerchantDetail from './merchant-detail'
    import UnauditMessage from './unaudit-message'
    import UnauditRecordReason from './unaudit-record-reason'

    export default {
        name: "merchant-list",
        data(){
            return {
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                unAudit:false,
                detailMerchant:null,
                query: {
                    name: '',
                    signBoardName:'',
                    auditStatus: [],
                    page: 1,
                    merchantId: '',
                    startDate: '',
                    endDate: '',
                    operName:'',
                    operId:'',
                    creatorOperName:'',
                    creatorOperId:''
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
                router.replace({
                    path: '/refresh',
                    query: {
                        name: 'MerchantList',
                        key: '/merchants'
                    }
                })
            },
            showMessage(scope){
                 api.get('merchant/audit/newlist', {id: scope.row.id}).then(data => {
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
                api.get('/merchants', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            detail(scope,type){
                router.push({
                    path: '/merchant/detail',
                    query: {id: scope.row.id,auditType:type},
                })
                return false;
            },
            //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
            audit(scope, type){
                if(type==2 ||type==1){
                    api.get('merchant/detail', {id: scope.row.id}).then(data => {
                        this.detailMerchant = data;
                        this.detailMerchant.type = type;
                        this.unAudit = true;
                    });
                }else{
                    let message = ['', '审核通过', '审核不通过', '打回到商户池'][type];
                    this.$confirm(`确定 ${message} 吗?`, scope.row.name).then(() => {
                        api.post('/merchant/audit', {id: scope.row.id, type: type}).then(data => {
                            this.$alert(message + ' 操作成功');
                            this.getList();
                        })
                    });
                }

            },
            downloadExcel() {
                let message = '确定要导出当前筛选的商户列表么？'
                this.query.startDate = this.query.startDate == null ? '' : this.query.startDate;
                this.query.endDate = this.query.endDate == null ? '' : this.query.endDate;
                this.$confirm(message).then(() => {
                    window.location.href = window.location.origin + '/api/admin/merchant/download?' + 'merchantId=' + this.query.merchantId + '&startDate=' + this.query.startDate + '&endDate=' + this.query.endDate + '&name=' + this.query.name + '&signBoardName='+ this.query.signBoardName+ '&auditStatus=' + this.query.auditStatus + '&operName=' + this.query.operName + '&operId=' + this.query.operId + '&creatorOperName=' + this.query.creatorOperName + '&creatorOperId=' + this.query.creatorOperId;
                })
            }
        },
        created(){
            if(this.isAudit){
                this.query.auditStatus=['0', '3']
                this.getList()

            }else{
                this.getList();
            }

        },
        components: {
            MerchantDetail,
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