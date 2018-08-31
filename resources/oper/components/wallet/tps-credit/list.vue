<template>
    <page title="我的TPS积分">
        <div class="group">
            <div class="item" v-for="item in credit">
                <p class="label">{{item.label}}</p >
                <p class="val">{{item.val}}</p >
            </div>
        </div>

        <el-form :model="query" inline size="small">
            <el-form-item prop="consumeQuotaNo" label="流水号">
                <el-input v-model="query.consumeQuotaNo" clearable/>
            </el-form-item>
            <el-form-item label="交易时间">
                <el-date-picker
                        v-model="query.startDate"
                        type="date"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        clearable
                >
                </el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="query.endDate"
                        type="date"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        clearable
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                >
                </el-date-picker>
            </el-form-item>
            <el-form-item label="TPS积分状态">
                <el-select v-model="query.status" placeholder="请选择" clearable class="w-150">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="冻结中" :value="1"></el-option>
                    <el-option label="已解冻待置换" :value="2"></el-option>
                    <el-option label="已置换" :value="3"></el-option>
                    <el-option label="已退款" :value="4"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="download">导出Excel</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="交易时间"></el-table-column>
            <el-table-column prop="consume_quota_no" label="流水号"></el-table-column>
            <el-table-column prop="type" label="交易类型">
                <template slot-scope="scope">
                    <span>下级贡献</span>
                </template>
            </el-table-column>
            <el-table-column prop="consume_user_mobile" label="用户手机号"></el-table-column>
            <el-table-column prop="sync_tps_credit" label="贡献积分"></el-table-column>
            <el-table-column prop="status" label="积分状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">冻结中</span>
                    <span v-else-if="scope.row.status == 2">已解冻待置换</span>
                    <span v-else-if="scope.row.status == 3">已置换</span>
                    <span v-else-if="scope.row.status == 3">已退款</span>
                    <span v-else>未知（{{scope.row.status}}）</span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope.row)">明 细</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="query.pageSize"
                :total="total"
        />
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-consume-list",
        data() {
            return {
                query: {
                    consumeQuotaNo: '',
                    startDate: '',
                    endDate: '',
                    status: 0,
                    page: 1,
                    pageSize: 15,
                },
                total: 0,
                list: [],
                tableLoading: false,

                totalShareTpsCredit: 0,
                theMonthShareTpsCredit: 0,
            }
        },
        computed: {
            credit() {
                return [
                    {
                        label: '累计获得下级贡献TPS积分',
                        val: this.totalShareTpsCredit
                    },
                    {
                        label: '本月累计获得下级贡献TPS积分',
                        val: this.theMonthShareTpsCredit,
                    },
                ];
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('/wallet/tpsCredit/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.totalShareTpsCredit = data.totalShareTpsCredit;
                    // this.theMonthShareTpsCredit = data.theMonthShareTpsCredit;
                    this.tableLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            download() {
                let query = this.query;
                location.href = '/api/oper/wallet/tpsCredit/exportExcel?consumeQuotaNo=' + query.consumeQuotaNo + '&startDate=' + query.startDate + '&endDate=' + query.endDate +'&status=' + query.status;
            },
            detail(row) {
                router.push({
                    path: '/wallet/credit/detail',
                    query: {
                        id: row.id,
                    }
                });
            }
        },
        created() {
            this.getList();
        },
    }
</script>

<style lang="less" scoped>
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
</style>