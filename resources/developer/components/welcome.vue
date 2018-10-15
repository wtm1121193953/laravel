<template>
    <el-row>
        <el-col>
            <div class="grid-content ">
                <h1>大千生活 -- 开发总后台</h1>
            </div>
        </el-col>
        <el-col :span="8">
            <el-form label-width="150px" title="系统信息">
                <el-form-item label="服务器路径">{{server['DOCUMENT_ROOT']}}</el-form-item>
                <el-form-item label="SERVER_NAME">{{server['SERVER_NAME']}}</el-form-item>
                <!--<el-form-item label="HTTP_REFERER">{{server['HTTP_REFERER']}}</el-form-item>-->
                <el-form-item label="MYSQL目录">{{server['MYSQL_HOME']}}</el-form-item>
                <el-form-item label="执行脚本">{{server['SCRIPT_FILENAME']}}</el-form-item>
                <el-form-item label="临时文件路径">{{server['TMP']}}</el-form-item>
                <el-form-item label="php版本">{{php.version}}</el-form-item>
                <el-form-item label="apache版本">{{apache.version}}</el-form-item>
                <el-form-item label="mysql版本">{{mysql.version}}</el-form-item>
            </el-form>
        </el-col>
        <el-col :span="1">
            <el-button type="text" @click="showPhpInfo">查看 phpinfo</el-button>
        </el-col>
    </el-row>
</template>

<script type="text/javascript">
    import api from '../../assets/js/api'

    export default {
        data() {
            return {
                server: {},
                php: {},
                apache: {},
                mysql: {},
            }
        },

        methods: {
            showPhpInfo(){
                window.open('/api/developer/home/phpinfo')
            }
        },

        created: function () {
            api.get('home/index').then(data => {
                this.server = data.server;
                this.php = data.php;
                this.apache = data.apache;
                this.mysql = data.mysql;
            });
        }
    }
</script>

<style scoped>
    .grid-content {
        /*text-align: center;*/
        border-radius: 4px;
        min-height: 36px;
    }


</style>