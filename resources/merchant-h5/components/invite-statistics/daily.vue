<template>
    <page title="我的会员">
        <el-table stripe :data="list">
            <el-table-column prop="date" label="注册日期"/>
            <el-table-column prop="invite_count" label="注册人数"/>
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
        name: "invite-statistics-daily",
        data() {
            return {
                list: [],
                total: 0,
                query: {
                    page: 1,
                },
            }
        },
        methods: {
            getList(){
                this.loading = true;
                api.get('invite/statistics/dailyList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.loading = false;
                })
            }
        },
        mounted(){
            this.getList();
        }
    }
</script>

<style scoped>

</style>