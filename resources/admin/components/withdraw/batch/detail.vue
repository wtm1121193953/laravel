<template>
    <page title="批次明细" :breadcrumbs="{提现批次管理: '/withdraw/batch'}">
        <el-col style="margin-bottom: 10px">
            <span class="span-top">第{{batchData.batch_no}}批</span>
            <span class="span-top" v-if="batchData.status == 1">结算中</span>
            <span class="span-top" v-else-if="batchData.status == 2">准备打款</span>
            <span class="span-top" v-else-if="batchData.status == 3">打款完成</span>
            <el-button v-if="batchData.status == 1" size="small" type="success" @click.native="preparePay">准备打款</el-button>
        </el-col>
        <div class="group">
            <div class="item" v-for="item in statistics">
                <p class="label">{{item.label}}</p >
                <p class="val">{{item.val}}</p >
            </div>
        </div>

        <el-form v-model="form" inline size="small">
            <el-form-item prop="withdrawNo" label="提现编号">
                <el-input v-model="form.withdrawNo" clearable class="w-200" placeholder="请输入提现编号"/>
            </el-form-item>
            <el-form-item prop="originType" label="提现类型">
                <el-select v-model="form.originType" placeholder="请选择" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option v-if="batchData.type == 2" label="用户提现" :value="1"></el-option>
                    <el-option label="商户提现" :value="2"></el-option>
                    <el-option v-if="batchData.type == 1" label="运营中心提现" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="提现状态">
                <el-select v-model="form.status" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="审核通过" :value="2"></el-option>
                    <el-option label="已打款" :value="3"></el-option>
                    <el-option label="打款失败" :value="4"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="提现时间">
                <el-date-picker
                        v-model="form.startDate"
                        type="date"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                ></el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="form.endDate"
                        type="date"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(form.startDate) - 8.64e7}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="exportExcel">导出Excel</el-button>
                <el-dropdown>
                    <el-button type="primary">
                        批量打款<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-if="hasRule('/api/admin/withdraw/record/paySuccess')" @click.native="batchPaySuccess(1)">打款成功</el-dropdown-item>
                        <el-dropdown-item v-if="hasRule('/api/admin/withdraw/record/payFail')" @click.native="batchPayFail(1)">打款失败</el-dropdown-item>
                        <el-dropdown-item v-if="hasRule('/api/admin/withdraw/record/paySuccess')" @click.native="batchPaySuccess(2)">全部打款成功</el-dropdown-item>
                        <el-dropdown-item v-if="hasRule('/api/admin/withdraw/record/payFail')" @click.native="batchPayFail(2)">全部打款失败</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe @selection-change="handleSelectionChange">
            <el-table-column
                    type="selection"
                    width="55"
                    :selectable="selectable">
            </el-table-column>
            <el-table-column prop="created_at" label="提现时间"></el-table-column>
            <el-table-column prop="withdraw_no" label="提现编号"></el-table-column>
            <el-table-column prop="batch_no" label="批次编号"></el-table-column>
            <el-table-column prop="bank_card_type" label="批次类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.bank_card_type == 2">对私</span>
                    <span v-else-if="scope.row.bank_card_type == 1">对公</span>
                    <span v-else>未知({{scope.row.bank_card_type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="origin_type" label="提现类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.origin_type == 1">用户提现</span>
                    <span v-else-if="scope.row.origin_type == 2">商户提现</span>
                    <span v-else-if="scope.row.origin_type == 3">运营中心提现</span>
                    <span v-else-if="scope.row.origin_type == 4">业务员提现</span>
                    <span v-else>未知({{scope.row.origin_type}})</span>
                </template>
            </el-table-column>
            </el-table-column>
            <el-table-column prop="bank_card_open_name" label="提现账户名称"></el-table-column>
            <el-table-column prop="amount" label="提现金额"></el-table-column>
            <el-table-column prop="charge_amount" label="手续费"></el-table-column>
            <el-table-column prop="remit_amount" label="到账金额"></el-table-column>
            <el-table-column prop="status" label="提现状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">审核中</span>
                    <span v-else-if="scope.row.status == 2">审核通过</span>
                    <span v-else-if="scope.row.status == 3">已打款</span>
                    <span v-else-if="scope.row.status == 4">
                        打款失败
                        <el-popover
                                placement="top-start"
                                title="备注"
                                width="200"
                                trigger="hover"
                                :content="scope.row.remark">
                            <div class="tips" slot="reference">{{scope.row.remark}}</div>
                        </el-popover>
                    </span>
                    <span v-else-if="scope.row.status == 5">
                        审核不通过
                        <el-popover
                                placement="top-start"
                                title="备注"
                                width="200"
                                trigger="hover"
                                :content="scope.row.remark">
                            <div class="tips" slot="reference">{{scope.row.remark}}</div>
                        </el-popover>
                    </span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope.row)">查看明细</el-button>
                    <el-button type="text" v-if="hasRule('/api/admin/withdraw/record/paySuccess') && scope.row.status == 2" @click="success(scope.row)">打款成功</el-button>
                    <el-button type="text" v-if="hasRule('/api/admin/withdraw/record/payFail') && scope.row.status == 2" @click="failed(scope.row)">打款失败</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="form.page"
                @current-change="getList"
                :page-size="form.pageSize"
                :total="total"
        ></el-pagination>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "withdraw-merchant-record",
        data() {
            return {
                form: {
                    withdrawNo: '',
                    originType: 0,
                    status: 0,
                    startDate: '',
                    endDate: '',
                    batchId: '',

                    page: 1,
                    pageSize: 15,
                },
                total: 0,
                batchData: {
                    amount: 0.00,
                    total: 0,
                    success_amount: 0.00,
                    success_total: 0,
                    failed_amount: 0.00,
                    failed_total: 0,
                    batch_no: '',
                    status: 0,
                },
                selection: [],

                list: [],
                tableLoading: false,
            }
        },
        computed: {
            statistics() {
                return [
                    {
                        label: '批次提现总金额/总笔数',
                        val: this.batchData.amount + '/' + this.batchData.total,
                    },
                    {
                        label: '打款成功金额/成功笔数',
                        val: this.batchData.success_amount + '/' + this.batchData.success_total,
                    },
                    {
                        label: '打款失败金额/失败笔数',
                        val: this.batchData.failed_amount + '/' + this.batchData.failed_total,
                    },
                ];
            },
            seletcionIds() {
                let ids = [];
                this.selection.forEach(function (item) {
                    ids.push(item.id);
                });
                return ids;
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('/withdraw/records', this.form).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.form.page = 1;
                this.getList();
            },
            exportExcel() {
                let data = this.form;
                let params = [];
                Object.keys(data).forEach((key) => {
                    let value =  data[key];
                    if (typeof value === 'undefined' || value == null) {
                        value = '';
                    }
                    params.push([key, encodeURIComponent(value)].join('='))
                }) ;
                let uri = params.join('&');

                location.href = `/api/admin/withdraw/record/export?${uri}`;
            },
            selectable(row, index) {
                if (row.status == 2) {
                    return true;
                } else {
                    return false;
                }
            },
            handleSelectionChange(val) {
                this.selection = val;
            },
            detail(row) {
                let path = '';
                if (row.origin_type == 1) {
                    path = '/withdraw/record/user';
                } else if (row.origin_type == 2) {
                    path = '/withdraw/record/merchant';
                } else if (row.origin_type == 3) {
                    path = '/withdraw/record/oper';
                } else if (row.origin_type == 4) {
                    path = '/withdraw/record/bizer';
                } else {
                    this.$message.error('提现类型错误');
                    return false;
                }

                router.push({
                    path: path,
                    query: {
                        id: row.id,
                    }
                });
            },
            success(row) {
                this.$confirm('<div class="tips">提现金额：'+row.amount+'</div><div>确定将这笔订单标记为打款成功！请确认您已打过款</div>','打款成功提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                }).then(() => {
                    api.post('/withdraw/record/paySuccess', {ids: row.id}).then(data => {
                        this.$alert('操作成功');
                        row.status = 3; // 已打款状态
                    })
                }).catch(() => {

                })
            },
            failed(row) {
                this.$prompt('<div>确定将这笔订单标记为打款失败！</div>','打款失败提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'textarea',
                    inputPlaceholder: '请填写失败原因，必填，最多50字',
                    inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                }).then(({value}) => {
                    let param = {
                        ids: row.id,
                        remark: value ? value : '',
                    };
                    api.post('/withdraw/record/payFail', param).then(data => {
                        this.$alert('操作成功');
                        row.status = 4; // 打款失败状态
                    })
                }).catch(() => {

                })
            },
            getBatchData() {
                api.get('/withdraw/batch/detail', {id: this.form.batchId}).then(data => {
                    this.batchData = data;
                })
            },
            batchPaySuccess(type) {
                let length = 0;
                let param = {};
                if (type == 1) {
                    length = this.seletcionIds.length;
                    param = {ids: this.seletcionIds};
                } else if (type == 2) {
                    length = this.total;
                    param = {batchId: this.form.batchId};
                } else {
                    this.$message.error('类型错误');
                    return;
                }
                if (length <= 0) {
                    this.$message.error('请选择打款订单');
                    return;
                }
                this.$confirm(`<div>确定将这${length}笔订单全部标记为打款成功！</div><div class="tips">请确认您已打过款</div>`,'打款成功提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                }).then(() => {
                    api.post('/withdraw/record/paySuccess', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            batchPayFail(type) {
                let length = 0;
                let param = {};
                if (type == 1) {
                    length = this.seletcionIds.length;
                    param = {ids: this.seletcionIds};
                } else if (type == 2) {
                    length = this.total;
                    param = {batchId: this.form.batchId};
                } else {
                    this.$message.error('类型错误');
                    return;
                }
                if (length <= 0) {
                    this.$message.error('请选择打款订单');
                    return;
                }
                this.$prompt(`<div>确定将这${length}笔订单全部标记为打款失败！</div>`,'打款失败提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'textarea',
                    inputPlaceholder: '请填写失败原因，必填，最多50字',
                    inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                }).then(({value}) => {
                    param.remark = value ? value : '';
                    api.post('/withdraw/record/payFail', param).then(data => {
                        this.$alert('操作成功');
                        this.getList();
                    })
                }).catch(() => {

                })
            },
            preparePay() {
                this.$confirm(`<div>确认将此批次标记成准备打款状态吗？</div><div>标记成功后不能再添加新的订单，此操作不可取消</div>`,'批次操作提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'info',
                    center: true,
                    dangerouslyUseHTMLString: true,
                }).then(() => {
                    api.post('/withdraw/batch/changeStatus', {id: this.form.batchId}).then(data => {
                        this.$alert('操作成功');
                        this.batchData = data;
                    })
                }).catch(() => {

                })
            }
        },
        created() {
            this.form.batchId = this.$route.query.id;
            if (!this.form.batchId) {
                this.$message.error('id不能为空');
                router.push('/withdraw/batch');
            }
            this.getBatchData();
            this.getList();
        }
    }
</script>

<style lang="less" scoped>
    .tips {
        overflow: hidden;
        text-overflow:ellipsis;
        white-space: nowrap;
    }
    .group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 30px 0;
        text-align: center;
        border: 1px solid #ccc;
        margin-bottom: 30px;

        & > * {
              border-right: 1px solid #ccc;
              padding: 15px 0;
          }

        .item {
            flex: 1;

            p {
                margin: 0;
            }

            .label {
                font-size: 14px;
            }

            .val {
                margin-top: 10px;
                font-size: 28px;
                font-weight: bold;
            }
        }

        .handler {
            flex: 1;
            border-right: 0 none;
        }

        .btn {
            width: 150px;
            height: 40px;
            line-height: 40px;
            margin: 0 auto;
            background: #999;
            color: #fff;
        }
    }
    .span-top {
        margin-right: 10px;
        font-weight: bold
    }
</style>