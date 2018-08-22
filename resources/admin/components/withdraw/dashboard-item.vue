<template>
    <el-col class="m-t-30">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all">
                <dashboard-detail :origin-type="originType" time-type="all"/>
            </el-tab-pane>
            <el-tab-pane label="今日" name="today">
                <dashboard-detail :origin-type="originType" time-type="today"/>
            </el-tab-pane>
            <el-tab-pane label="昨日" name="yesterday">
                <dashboard-detail :origin-type="originType" time-type="yesterday"/>
            </el-tab-pane>
            <el-tab-pane label="本月" name="month">
                <dashboard-detail :origin-type="originType" time-type="month"/>
            </el-tab-pane>
            <el-tab-pane label="上个月" name="last-month">
                <dashboard-detail :origin-type="originType" time-type="last-month"/>
            </el-tab-pane>
            <el-tab-pane label="其他时间" name="other">
                <dashboard-detail :origin-type="originType" time-type="other"/>
            </el-tab-pane>
        </el-tabs>
    </el-col>
</template>

<script>
    import DashboardDetail from './dashboard-detail'
    export default {
        name: "withdraw-dashboard-item",
        props: {
            originType: String,
        },
        data(){
            return {
                activeName: 'today'
            }
        },
        components: {
            DashboardDetail
        }
    }
</script>

<style scoped>

</style>