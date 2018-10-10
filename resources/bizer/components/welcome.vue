
<template>
    <el-row>
        <el-col :span="7"><div class="grid-content "></div></el-col>
        <el-col :span="10"><div class="grid-content ">
            <h1 >欢迎使用大千生活业务员系统</h1>
            <img src="../../assets/images/welcome.png" class="welcome">
        </div></el-col>
        <el-col :span="7"><div class="grid-content "></div></el-col>
    </el-row>
</template>

<script type="text/javascript">

    export default {

        created: function(){

        }
    }
</script>

<style scoped>
    .grid-content {
        text-align: center;
        border-radius: 4px;
        min-height: 36px;
        margin-top: 100px;
    }
    h1{
        font-size: 34px;
    }
</style>