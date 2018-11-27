<template>
        <el-col :span="24">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col>
                    <div class="title">审核信息</div>
                </el-col>

                <el-row>
                    <el-col :span="11">
                        <el-form-item label="审核ID">
                            <span>{{reversedData.audit.id}}</span>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :span="11">
                        <el-form-item label="操作类型">
                            <span v-if="reversedData.audit.type === 1">新增商户</span>
                            <span v-else-if="reversedData.audit.type === 2">更新商户信息</span>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :span="11">
                        <el-form-item label="审核时间 ">
                            <span>{{reversedData.audit.audit_time}}</span>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :span="11">
                        <el-form-item label="提交审核时间">
                            <span>{{reversedData.audit.created_at}}</span>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :span="11">
                        <el-form-item label="审核结果">
                            <span class="c-warning" v-if="reversedData.audit.status === 1">待审核</span>
                            <span class="c-green" v-if="reversedData.audit.status === 2">审核通过</span>
                            <span class="c-danger" v-if="reversedData.audit.status === 3">审核不通过</span>
                            <span class="c-warning" v-if="reversedData.audit.status === 4">已撤回</span>
                        </el-form-item>
                    </el-col>
                </el-row>


                <el-row>
                    <el-col :span="11">
                        <el-form-item label="审核意见">
                            <span>{{reversedData.audit.suggestion||'无'}}</span>
                        </el-form-item>
                    </el-col>
                </el-row>

            </el-form>

        </el-col>

</template>

<script>
    import 'viewerjs/dist/viewer.css'

    export default {
        name: 'audit-message',
        props: {
            data: Object,
            type: String,
            auditType:Number,
        },
        computed:{
            reversedData:function(){
                if(this.data.audit===undefined){
                    this.data.audit = {
                        id:'',
                        type:'',
                        audit_time:'',
                        created_at:'',
                        status:'',
                        suggestion:''
                    }
                }
                return this.data;
            }
        },
        data(){
            return {

            }
        },
        methods: {

        },
        created(){

        },
        components: {

        },
    }

</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>