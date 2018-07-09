<template>
    <el-table  :data = "data" >
        <el-table-column width="150" property="audit_suggestion" label="审核意见"></el-table-column>
    </el-table>
</template>

<script>
    export default {
        name: 'unaudit-record-reason',
        props: {
            data: {
                type:Array,
            },
        },
    }
</script>

<style scoped>

</style>