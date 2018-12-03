<template>
    <el-col>
        <el-form v-model="form" inline size="small">
            <el-form-item prop="withdrawNo" label="提现编号">
                <el-input v-model="form.withdrawNo" clearable placeholder="请输入提现编号" class="w-200"/>
            </el-form-item>
            <el-form-item prop="bizerId" label="业务员ID">
                <el-input v-model="form.bizerId" clearable placeholder="请输入业务员ID"/>
            </el-form-item>
            <el-form-item prop="bizerMobile" label="业务员手机号">
                <el-input v-model="form.bizerMobile" clearable placeholder="请输入业务员手机号"/>
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
            <el-form-item label="提现状态">
                <el-select v-model="form.status" multiple clearable class="w-200">
                    <el-option label="审核中" :value="1"></el-option>
                    <el-option label="审核通过" :value="2"></el-option>
                    <el-option label="已打款" :value="3"></el-option>
                    <el-option label="打款失败" :value="4"></el-option>
                    <el-option label="审核不通过" :value="5"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="提现时间" width="150px"></el-table-column>
            <el-table-column prop="withdraw_no" label="提现编号" width="200px"></el-table-column>
            <el-table-column prop="amount" label="提现金额"></el-table-column>
            <el-table-column prop="charge_amount" label="手续费"></el-table-column>
            <el-table-column prop="remit_amount" label="到账金额"></el-table-column>
            <el-table-column prop="origin_id" label="业务员ID"></el-table-column>
            <el-table-column prop="bizer_name" label="业务员"></el-table-column>
            <el-table-column prop="bizer_mobile" label="业务员手机号码"></el-table-column>
            <el-table-column prop="bank_card_type" label="账户类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.bank_card_type == 2">个人</span>
                    <span v-else-if="scope.row.bank_card_type == 1">公司</span>
                    <span v-else>未知({{scope.row.bank_card_type}})</span>
                </template>
            </el-table-column>
            <!--<el-table-column prop="bank_card_no" label="提现账号">
                <template slot-scope="scope">
                    {{scope.row.bank_card_no.substr(0,5) + '****' + scope.row.bank_card_no.substr(-4,4)}}
                </template>
            </el-table-column>-->
            <!--<el-table-column prop="bank_card_open_name" label="账户名"></el-table-column>-->
            <!--<el-table-column prop="bank_name" label="开户行"></el-table-column>-->
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
                    <el-button type="text" v-if="hasRule('/api/admin/withdraw/record/audit') && scope.row.status == 1" @click="audit(scope.row)">审 核</el-button>
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
    </el-col>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "withdraw-user-record",
        props: {
            type: {
                type: String,
                default: '',
            },
            status: {
                type: Array,
                default: [],
            },
            queryStartDate: {
                type: String,
                default: '',
            },
            queryEndDate: {
                type: String,
                default: '',
            }
        },
        data() {
            return {
                form: {
                    withdrawNo: '',
                    bizerId: '',
                    bizerMobile: '',
                    startDate: '',
                    endDate: '',
                    status: [],

                    // 商户提现记录
                    originType: 4,

                    page: 1,
                    pageSize: 15,
                },
                total: 0,

                list: [],
                tableLoading: false,

                remark: '',
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
            detail(row) {
                router.push({
                    path: '/withdraw/record/bizer',
                    query: {
                        id: row.id,
                    }
                });
            },
            audit(row) {
                router.push({
                    path: '/withdraw/record/bizer',
                    query: {
                        id: row.id,
                        audit: true,
                        type: row.bank_card_type, //批次类型
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
            }
        },
        created() {
            if (this.type == 'bizer') {
                this.form.status = this.status;
                this.form.startDate = this.queryStartDate;
                this.form.endDate = this.queryEndDate;
            }
            this.getList();
        }
    }
</script>

<style scoped>
    .tips {
        overflow: hidden;
        text-overflow:ellipsis;
        white-space: nowrap;
    }
</style>