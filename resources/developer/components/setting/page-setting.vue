<template>
    <page title="页面配置">
        <el-table :data="tableData" style="width: 30%;">
            <el-table-column type="index" label="序号" align="center"></el-table-column>
            <el-table-column prop="title" label="页面" align="center"></el-table-column>
            <el-table-column label="操作" align="center" width="100">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope.row)">编 辑</el-button>
                </template>
            </el-table-column>
        </el-table>
    </page>
</template>

<script>
    export default {
        name: "page-setting",
        data() {
            return {
                tableData: [
                    {
                        title: '积分规则',
                        code: 'credit_rules',
                    },
                    {
                        title: '等级规则',
                        code: 'grade_rules',
                    }
                ]
            }
        },
        methods: {
            edit(row) {
                router.push({
                    path: '/setting/page_setting_form',
                    query: {
                        code: row.code,
                        title: row.title,
                    }
                })
            }
        }
    }
</script>

<style scoped>

</style>