<template>
    <!-- Vue file -->
    <div>
        <el-breadcrumb>
            <el-breadcrumb-item @click.native="$menu.change('/provider/index')">首页</el-breadcrumb-item>
            <el-breadcrumb-item>404页面</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content">
            <div class="page-title">
                404页面
            </div>

            <div>
                <el-col :span="12">
                    <router-link to="/demo/index"><el-button type="text">返回首页</el-button></router-link>
                    <p>啊 ~ 到手的鸭子飞了!</p>
                </el-col>
            </div>
        </el-row>
    </div>
</template>
<script>
    export default {
        data: function () {
            return {}
        },
        methods: {},
        created: function () {

        }
    }
</script>