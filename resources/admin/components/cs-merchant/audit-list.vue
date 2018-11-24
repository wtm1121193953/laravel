<template>
    <page title="审核商户" v-loading="isLoading">
        <el-col>
            <el-form v-model="query" inline>
                <el-form-item prop="merchantId" label="商户ID">
                    <el-input v-model="query.merchantId" size="small" placeholder="商户ID" class="w-100" clearable></el-input>
                </el-form-item>
                <el-form-item prop="name" label="商户名称">
                    <el-input v-model="query.name" size="small" placeholder="商户名称" clearable
                              @keyup.enter.native="search"/>
                </el-form-item>
                <el-form-item prop="operId" label="运营中心ID">
                    <el-input v-model="query.operId" size="small" placeholder="激活运营中心ID" class="w-100" clearable/>
                </el-form-item>
                <el-form-item label="审核状态" prop="auditStatus">
                    <el-select v-model="query.auditStatus" size="small" multiple placeholder="请选择">
                        <el-option label="待审核" value="1"/>
                        <el-option label="审核通过" value="2"/>
                        <el-option label="审核不通过" value="3"/>
                        <el-option label="已撤回" value="4"/>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="search"><i class="el-icon-search">搜 索</i></el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="添加时间" width="160px"/>
            <el-table-column prop="id" size="mini" label="商户ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="name" label="商户类型">
                <template slot-scope="scope">
                    <span>超市类</span>
                </template>
            </el-table-column>
            <el-table-column prop="data_after.signboard_name" label="商户招牌名"/>
            <el-table-column prop="oper_id" size="mini" label="运营中心ID"/>
            <el-table-column prop="operName" label="运营中心名称"/>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <span> {{ scope.row.data_after.city }} </span>
                    <span> {{ scope.row.data_after.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="商户状态">
                <template slot-scope="scope" v-if="scope.row.cs_merchant_detail && (scope.row.status == 2 || scope.row.status == 3)">
                    <span v-if="scope.row.cs_merchant_detail.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.cs_merchant_detail.status === 2" class="c-danger">已冻结</span>
                    <span v-else>未知 ({{scope.row.cs_merchant_detail.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-warning">待审核</span>
                    <el-popover
                            v-else-if="scope.row.status === 2"
                            placement="bottom-start"
                            width="200px" trigger="hover"
                            @show="showMessage(scope)">
                        <div slot="reference" class="c-green"><p>审核通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.suggestion || '无'}}
                        <!--<unaudit-record-reason :data="auditRecord"/>-->
                    </el-popover>
                    <el-popover
                            v-else-if="scope.row.status === 3"
                            placement="bottom-start"
                            width="200px" trigger="hover"
                            @show="showMessage(scope)">
                        <div slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.suggestion || '无'}}
                        <!--<unaudit-record-reason :data="auditRecord"/>-->
                    </el-popover>
                    <span v-else-if="scope.row.status == 4" class="c-gray">已撤回</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="cs_merchant_detail.settlement_cycle_type" label="结算周期">
                <template slot-scope="scope">
                    <span>{{ {1: '周结', 2: '半月结', 3: 'T+1(自动)', 4: '半年结', 5: '年结', 6: 'T+1(人工)', 7: '未知',}[scope.row.data_after.settlement_cycle_type] }}</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope)">查看</el-button>
                    <template v-if="scope.row.status === 1 || scope.row.status === 3">
                        <el-button type="text" @click="detail(scope,3)">审核</el-button>
                        <el-dropdown trigger="click" @command="(command) => {audit(scope, command)}">
                            <el-button type="text">
                                快捷审核 <i class="el-icon-arrow-down"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="1">审核通过</el-dropdown-item>
                                <el-dropdown-item command="2">审核不通过</el-dropdown-item>
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
        <el-dialog title="审核意见" :visible.sync="showAudit" :close-on-click-modal="false">
            <el-row>
                <el-form v-if="currentAuditRecord" label-width="120px" label-position="left" size="small">
                    <!--商户录入信息左侧块-->
                    <el-col :span="11">
                        <el-form-item prop="name" label="商户名称">{{currentAuditRecord.name}}</el-form-item>
                    </el-col>
                    <el-col>
                        <el-form-item prop="audit_suggestion" label="审核意见">
                            <el-input placeholder="最多输入50个汉字，可不填" maxlength="50" v-model="auditForm.audit_suggestion"
                                      :autosize="{minRows: 3}" type="textarea"/>
                        </el-form-item>
                    </el-col>

                    <!-- 商户激活信息右侧块 -->
                    <el-col>
                        <el-form-item>
                            <el-button @click="showAudit = false">取消</el-button>
                            <el-button type="primary" @click="doAudit">确定</el-button>
                        </el-form-item>

                    </el-col>
                </el-form>
            </el-row>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import CsMerchantDetail from './merchant-detail'

    export default {
        name: "cs-merchant-list",
        data() {
            return {
                activeTab: 'merchant',
                showDetail: false,
                isLoading: false,
                detailMerchant: null,
                query: {
                    merchantId: '',
                    name: '',
                    operId: '',
                    status: '',
                },
                list: [],
                auditRecord: [],
                total: 0,
                currentMerchant: null,
                tableLoading: false,
                showAudit: false,
                fastAuditType: 1,
                auditForm: {
                    audit_suggestion: ''
                },
                currentAuditRecord : null,
            }
        },
        computed: {
            isAudit() {
                let isAudit = this.$route.path;
                return isAudit == "/merchant/unaudits"
            }
        },
        methods: {
            merchantChange() {
                this.getList();
            },
            showMessage(scope) {
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
                if (this.query.auditStatus.length == 0 && this.isAudit) {
                    this.query.auditStatus = ['0', '3']
                }
                this.query.page = 1;
                this.getList();
            },
            getList() {
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/cs/merchant/audit/list', params).then(data => {
                    console.log(data.list);
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            detail(scope, type) {
                let self = this;
                router.push({
                    path: 'cs/merchant/unaudits/detail',
                    name: 'CsMerchantUnauditDetail',
                    query: {
                        id: scope.row.id,
                        auditType: type,
                        isAudit: this.isAudit,
                    },
                    params: self.query,
                })
            },

            edit(scope) {
                router.push({
                    path: '/cs/merchant/edit',
                    query: {id: scope.row.id},
                })
                return false;
            },
            //type: 1-审核通过  2-审核不通过
            audit(scope, type) {
                this.fastAuditType = type;
                this.showAudit = true;
                this.currentAuditRecord = scope.row;
                this.auditForm.audit_suggestion = '';
            },
            doAudit(){
                let auditTypeMessage = this.fastAuditType == 1 ? '审核通过' : '审核不通过';
                if(this.fastAuditType == 2 && !this.auditForm.audit_suggestion){
                    this.$message.error('请输入审核不通过意见');
                    return ;
                }
                this.$confirm(`确定将商户 ${this.currentAuditRecord.name} ${auditTypeMessage}吗?`).then(() => {
                    let reqData = {id: this.currentAuditRecord.id, type: this.fastAuditType,audit_suggestion:this.auditForm.audit_suggestion};
                    api.post('/cs/merchant/audit', reqData).then(data => {
                        this.$message.success(['', '审核通过', '审核不通过', '打回商户池'][this.fastAuditType] + ' 成功');
                        this.getList();
                        this.showAudit = false;
                    })
                })
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
                        let value = data[key];
                        if (typeof value === 'undefined' || value == null) {
                            value = '';
                        }
                        params.push([key, encodeURIComponent(value)].join('='))
                    });
                    let uri = params.join('&');

                    location.href = `/api/admin/cs/merchant/export?${uri}`;
                })
            },
            changeStatus(row) {
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
        created() {
            if (this.isAudit) {
                this.query.auditStatus = ['0', '3'];
                Object.assign(this.query, this.$route.params);
                this.getList();
            } else {
                Object.assign(this.query, this.$route.params);
                this.getList();
            }

        },
        components: {
            CsMerchantDetail,
        }
    }
</script>

<style scoped>
    .message {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 120px;
        font-size: 12px;
        color: gray;
    }

</style>