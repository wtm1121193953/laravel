<template>
    <page title="账户总览">
        <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
            <el-tab-pane label="全部提现汇总" name="all">
                <dashboard-item origin-type="all"></dashboard-item>
            </el-tab-pane>
            <el-tab-pane label="商户提现汇总" name="merchant">
                <dashboard-item origin-type="merchant"></dashboard-item>
            </el-tab-pane>
            <el-tab-pane label="用户提现汇总" name="user">
                <dashboard-item origin-type="user"></dashboard-item>
            </el-tab-pane>
            <el-tab-pane label="运营中心提现汇总" name="oper">
                <dashboard-item origin-type="oper"></dashboard-item>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import DashboardItem from './dashboard-item'
    export default {
        name: "withdraw-dashboard",
        data(){
            return {
                activeName: 'all',

            }
        },
        components: {
            DashboardItem
        }
    }
</script>

<style scoped>

</style>