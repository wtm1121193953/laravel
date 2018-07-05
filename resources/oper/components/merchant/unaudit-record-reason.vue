<template>
    <el-row>
        <el-col :span="24">
            <el-form  size="small">
                <el-col  :span="10">
                    <el-form-item prop="audit_suggestion" >
                        <p>审核意见</p>
                        <span>{{data.audit_suggestion}}</span>
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

    }

</script>

<style scoped>

</style>