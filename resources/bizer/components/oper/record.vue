<template>
    <page title="申请记录" v-loading="isLoading">
    <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="申请签约时间"/>
            <el-table-column prop="name" label="运营中心名称">
                <template slot-scope="scope">
                    <span> {{ scope.row.operInfo.name }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="oper_id" label="运营中心ID"/>
            <el-table-column prop="status" label="签约状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1">已签约</span>
                    <span v-else-if="scope.row.status === -1">已拒绝</span>
                    <span v-else-if="scope.row.status === 0">待签约</span>
                </template>
            </el-table-column>
        <el-table-column prop="remark" label="备注">
            <template slot-scope="scope">
                <span v-if="scope.row.status == 0">{{scope.row.remark}}</span>
                <span v-else-if="scope.row.status == 1 || scope.row.status == -1">{{scope.row.note}}</span>
                <span v-else></span>
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
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1
                },
                list: [],
                total: 0
            }
        },
        computed: {

        },
        methods: {
            getList(){
                this.isLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/opersRecord', params).then(data => {
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
        },
        created(){
            this.getList();
        },
        components: {

        }
    }
</script>

<style scoped>

</style>
