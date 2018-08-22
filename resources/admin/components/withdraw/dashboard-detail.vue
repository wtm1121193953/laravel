<template>
    <el-col :span="20">
        <el-row type="flex" class="h-200" align="middle" justify="space-around">
            <el-col :span="6">
                <el-card class="card">
                    <div class="show" v-if="originType != 'all'">
                        <el-button type="text" @click="goWithdrawRecords('all')">查看</el-button>
                    </div>
                    <div>提现总金额/总笔数</div>
                    <div class="number">{{totalAmount.toFixed(2)}}/{{totalCount}}</div>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card class="card">
                    <div class="show" v-if="originType != 'all'">
                        <el-button type="text" @click="goWithdrawRecords('success')">查看</el-button>
                    </div>
                    <div>提现成功金额/成功笔数</div>
                    <div class="number">{{successAmount.toFixed(2)}}/{{successCount}}</div>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card class="card">
                    <div class="show" v-if="originType != 'all'">
                        <el-button type="text" @click="goWithdrawRecords('fail')">查看</el-button>
                    </div>
                    <div>提现失败金额/失败笔数</div>
                    <div class="number">{{failAmount.toFixed(2)}}/{{failCount}}</div>
                </el-card>
            </el-col>
        </el-row>
    </el-col>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "dashboard-detail",
        props: {
            originType: {type: String, default: 'all'},
            timeType: {type: String, default: 'today'},
        },
        data(){
            return {
                totalAmount: 0.00,
                totalCount: 0,
                successAmount: 0.00,
                successCount: 0,
                failAmount: 0.00,
                failCount: 0,
            }
        },
        methods: {
            getData(){
                api.get('/withdraw/dashboard', {
                    originType: this.originType,
                    timeType: this.timeType,
                }).then(data => {
                    this.totalAmount = data.totalAmount;
                    this.totalCount = data.totalCount;
                    this.successAmount = data.successAmount;
                    this.successCount = data.successCount;
                    this.failAmount = data.failAmount;
                    this.failCount = data.failCount;
                })
            },
            goWithdrawRecords(type){
                this.$menu.change('/withdraw/record')
            }
        },
        created(){
            this.getData();
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
</style>