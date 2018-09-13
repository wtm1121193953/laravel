<template>
    <page title="商户审核记录">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="提交审核时间"/>
            <el-table-column prop="updated_at" label="审核时间"/>
            <el-table-column prop="merchantName" label="商户名称"/>
            <el-table-column prop="operName" label="运营中心"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 0" class="c-warning">待审核</span>
                    <span v-else-if="scope.row.status === 1" class="c-green">审核通过</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">审核不通过</span>
                    <span v-else-if="scope.row.status === 3" class="c-warning">待审核(重新提交)</span>
                    <span v-else-if="scope.row.status === 4" class="c-danger">已打回商户池</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"/>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "audit-list",
        data() {
            return {
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
            }
        },
        methods: {
            getList(){
                api.get('/merchant/audit/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            }
        },
        created(){
            this.getList();
        }
    }
</script>

<style scoped>

</style>