<template>
    <page title="命令执行">
        <el-form label-width="50px" size="small">
            <el-form-item>
                <el-button type="primary" @click="queueRestart" :disabled="optionDisabled" :loading="btnLoadingQueueRestart">
                    重启队列 (queue:restart)
                </el-button>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="updateCode" :disabled="optionDisabled" :loading="btnLoadingUpdateCode">
                    更新代码 (git pull)
                </el-button>
            </el-form-item>
            <el-form-item>
                <el-dropdown @command="(command) => {npmBuild(command)}" placement="bottom-start" >
                    <el-button :loading="btnLoadingNpmBuild" :disabled="optionDisabled" type="primary">
                        npm代码构建 <i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item command="dev">dev环境 (npm run dev)</el-dropdown-item>
                        <el-dropdown-item command="prod">prod环境 (npm run prod)</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-form-item>
            <el-form-item>
            </el-form-item>
        </el-form>

        <el-dialog title="操作结果" :visible.sync="showResult" :close-on-click-modal="false">
            <pre>{{result}}</pre>
            <el-button class="m-t-20" @click="showResult = false">关闭</el-button>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "command",
        data(){
            return {
                btnLoadingQueueRestart: false,
                btnLoadingUpdateCode: false,
                btnLoadingNpmBuild: false,

                result: '',
                showResult: false,
            }
        },
        computed: {
            optionDisabled(){
                return this.btnLoadingQueueRestart || this.btnLoadingUpdateCode || this.btnLoadingNpmBuild
            }
        },
        methods: {
            queueRestart(){
                this.btnLoadingQueueRestart = true;
                this.sendCommand('queue:restart').then(() => {
                    this.btnLoadingQueueRestart = false;
                })
            },
            updateCode(){
                this.$confirm('确定要更新代码吗?').then(() => {
                    this.btnLoadingUpdateCode = true;
                    this.sendNativeCommand('git pull').then(() => {
                        this.btnLoadingUpdateCode = false;
                    });
                })
            },
            npmBuild(type){
                this.$confirm('确定要构建前端代码吗?').then(() => {
                    this.btnLoadingNpmBuild = true;
                    this.sendNativeCommand('npm run ' + type).then(() => {
                        this.btnLoadingNpmBuild = false;
                    });
                })
            },
            sendCommand(command){
                return api.post('system/command', {command}).then(data => {
                    this.showResult = true;
                    this.result = data.result;
                    // this.$alert(data.result);
                });
            },
            sendNativeCommand(command){
                return api.post('system/command/native', {command}).then(data => {
                    this.showResult = true;
                    this.result = data.result;
                    /*this.$alert(`<pre>${data.result}</pre>`, '操作结果', {
                        dangerouslyUseHTMLString: true
                    });*/
                });
            }
        },
        created(){
            axios.defaults.timeout = 1000 * 300; // ajax超时时间设置为300秒
        },
        beforeDestroy(){
            axios.defaults.timeout = 1000 * 15; // ajax超时时间还原为15秒
        }
    }
</script>

<style scoped>

</style>