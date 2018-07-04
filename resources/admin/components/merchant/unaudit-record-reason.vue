<template>
    <el-row>
        <el-col :span="24">
            <el-form  size="small">
                <el-col  :span="10">
                    <el-form-item prop="audit_suggestion" label="审核意见">
                        {{data.audit_suggestion}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-col>
    </el-row>
</template>

<script>

    export default {
        name: 'unaudit-record-reason',
        props: {
            data: Object,
        },
        data(){
            return {
                audit_suggestion:''
            }
        },

    }

</script>

<style scoped>

</style>