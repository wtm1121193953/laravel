<template>
    <!-- Vue file -->
    <div>
        <el-breadcrumb>
            <el-breadcrumb-item>首页</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content">
            <div class="page-title">
                首页
            </div>

            <div>
                <el-col :offset="2" :span="16">
                    <h1 class="title">欢迎来到运营服务平台-BOSS系统</h1>
                </el-col>
            </div>
        </el-row>
    </div>
</template>
<script>
    export default {
        data: function () {
            return {
                sysinfo: []
            }
        },
        methods: {},
        created: function () {

        }
    }
</script>
<style scoped>
    .title {
        margin: 20px;
        text-align: center;
        color: #475669;
    }
</style>