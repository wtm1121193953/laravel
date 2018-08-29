<template>
    <page title="账户总览">
        <el-tabs v-model="originType" type="card" @tab-click="originTypeChange">
            <el-tab-pane label="全部提现汇总" name="all"></el-tab-pane>
            <el-tab-pane label="商户提现汇总" name="merchant"></el-tab-pane>
            <el-tab-pane label="用户提现汇总" name="user"></el-tab-pane>
            <el-tab-pane label="运营中心提现汇总" name="oper"></el-tab-pane>
        </el-tabs>
        <el-col class="m-t-30 search-bar">

            <el-radio-group v-model="timeType" size="small">
                <el-radio label="all" border>全部</el-radio>
                <el-radio label="today" border>今日</el-radio>
                <el-radio label="yesterday" border>昨日</el-radio>
                <el-radio label="month" border>本月</el-radio>
                <el-radio label="lastMonth" border>上个月</el-radio>
                <el-radio label="other" border>其他时间</el-radio>
            </el-radio-group>
            <template v-if="timeType == 'other'">
                <el-date-picker
                        v-model="dateRange"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd"
                        size="small"
                >
                </el-date-picker>
                <el-button type="primary" size="small" @click="getData">查询</el-button>
            </template>

            <!--<el-tabs v-model="timeType">
                <el-tab-pane label="全部" name="all"></el-tab-pane>
                <el-tab-pane label="今日" name="today"></el-tab-pane>
                <el-tab-pane label="昨日" name="yesterday"></el-tab-pane>
                <el-tab-pane label="本月" name="month"></el-tab-pane>
                <el-tab-pane label="上个月" name="lastMonth"></el-tab-pane>
                <el-tab-pane label="其他时间" name="other"></el-tab-pane>
            </el-tabs>-->
            <div class="fr" v-if="originType == 'oper'">
                运营中心
                <el-select size="small" v-model="operId" placeholder="全部">
                    <el-option value="">全部</el-option>
                    <el-option v-for="oper in opers" :key="oper.id" :value="oper.id">{{oper.name}}</el-option>
                </el-select>
            </div>
            <div class="fr" v-if="originType == 'merchant'">
                商户
                <el-select size="small" v-model="merchantId" placeholder="全部">
                    <el-option value="">全部</el-option>
                    <el-option v-for="merchant in merchants" :key="merchant.id" :value="merchant.id">{{merchant.name}}</el-option>
                </el-select>
            </div>
        </el-col>
        <el-col>
            <span class="search-type-name">
                {{searchTypeName}}
            </span>
        </el-col>

        <el-col :span="20" v-loading="dataLoading">
            <el-row type="flex" class="h-200" align="middle" justify="space-around">
                <el-col :span="6">
                    <el-card class="card">
                        <div class="show" v-if="originType != 'all'">
                            <el-button type="text" @click="goWithdrawRecords('all')">查看</el-button>
                        </div>
                        <div>提现总金额/总笔数</div>
                        <div class="number">{{totalAmount}}/{{totalCount}}</div>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="card">
                        <div class="show" v-if="originType != 'all'">
                            <el-button type="text" @click="goWithdrawRecords('success')">查看</el-button>
                        </div>
                        <div>提现成功金额/成功笔数</div>
                        <div class="number">{{successAmount}}/{{successCount}}</div>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="card">
                        <div class="show" v-if="originType != 'all'">
                            <el-button type="text" @click="goWithdrawRecords('fail')">查看</el-button>
                        </div>
                        <div>提现失败金额/失败笔数</div>
                        <div class="number">{{failAmount}}/{{failCount}}</div>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "withdraw-dashboard",
        data(){
            return {
                opers: [],
                merchants: [],
                originType: 'all',
                timeType: 'today',
                dateRange: [],
                operId: '',
                merchantId: '',
                dataLoading: false,
                searchTypeName: '全部统计汇总 > 今日',

                totalAmount: 0.00,
                totalCount: 0,
                successAmount: 0.00,
                successCount: 0,
                failAmount: 0.00,
                failCount: 0,
            }
        },
        computed: {
        },
        methods: {
            getOriginTypeText(){
                return {
                    'all': '全部提现汇总',
                    'merchant': '商户提现汇总',
                    'user': '用户提现汇总',
                    'oper': '运营中心提现汇总'
                }[this.originType]
            },
            getTimeTypeText(){
                let types = {
                    'all': '全部',
                    'today': '今日',
                    'yesterday': '昨日',
                    'month': '本月',
                    'lastMonth': '上个月',
                    'other': '其他时间',

                };
                return types[this.timeType]
            },
            getData(){
                let originText = this.getOriginTypeText();
                let timeText = this.getTimeTypeText();
                this.searchTypeName = originText + ' > ' + timeText;

                if(this.timeType == 'other' && this.dateRange.length < 2){
                    this.$message.error('请选择起始日期');
                    return false;
                }

                let query = {
                    originType: this.originType,
                    timeType: this.timeType,
                    startDate: this.dateRange[0] || '',
                    endDate: this.dateRange[1] || '',
                    operId: this.operId,
                    merchantId: this.merchantId,
                };
                console.log(query)
                this.dataLoading = true;
                api.get('/withdraw/dashboard', query).then(data => {
                    this.totalAmount = data.totalAmount;
                    this.totalCount = data.totalCount;
                    this.successAmount = data.successAmount;
                    this.successCount = data.successCount;
                    this.failAmount = data.failAmount;
                    this.failCount = data.failCount;
                }).finally(() => {
                    this.dataLoading = false;
                })
            },
            goWithdrawRecords(status){
                this.$menu.change('/withdraw/records?type=' + this.originType + '&status=' + status)
                store.commit('setCurrentMenu', '/withdraw/records');
            },
            originTypeChange(){
                this.timeType = 'today';
                this.getData();
            }
        },
        created(){
            this.getData();
        },
        watch: {
            timeType(val){
                if(val != 'other'){
                    this.getData();
                }
            },
            operId(){
                if(this.originType == 'oper'){
                    this.getData();
                }
            },
            merchantId(){
                if(this.originType == 'merchant'){
                    this.getData();
                }
            },
        }
    }
</script>

<style scoped>

    .card {
        text-align: center;
        align-items: center;
        font-size: 14px;
        position: relative;
    }
    .number {
        font-weight: bolder;
        font-size: 28px;
        margin-top: 15px;
    }
    .show {
        position: absolute;
        right: 20px;
        top: 10px;
    }
    .search-bar {
        padding-bottom: 10px;
        border-bottom: #ebeef4 1px solid;
        font-size: 14px;
    }
    .search-type-name{
        font-size: 14px;
        color: #676767;
        margin-top: 30px;
        line-height: 50px;
    }
</style>