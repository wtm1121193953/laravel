<template>

    <page title="自动打款管理" v-loading="isLoading">

        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="create_date" label="日期" />
            <el-table-column prop="type" label="商户类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.merchant_type === 1">普通商户</span>
                    <span v-else-if="scope.row.merchant_type === 2">超市商户</span>
                </template>
            </el-table-column>
            <el-table-column prop="batch_no" label="批次号"  />
            <el-table-column prop="amount" label="需代付总金额"  width="160px" />
            <el-table-column prop="status" label="确认自动打款">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 0" class="c-warning">未确认</span>
                    <span v-else-if="scope.row.status === 1" class="c-green">已确认</span>
                    <span v-else-if="scope.row.status === 2" class="c-green">已完成</span>
                    <span v-else-if="scope.row.status === -1" class="c-danger">失败</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="type" label="类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type === 1" class="c-green">首次打款</span>
                    <span v-else-if="scope.row.type === 2" class="c-green">重新打款</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="150px">
                <template slot-scope="scope">
                    <el-button type="text" v-if="parseInt(scope.row.status) === 0" @click="modifyPlatformStatus(scope)">确认打款</el-button>
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
        name: "settlement-platform-batches",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
                tableLoading: false
            }
        },
        methods: {
            getList(){
                this.tableLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/settlementPlatformBatches/list', params).then(data => {
                    this.query.page = params.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                }).finally(() => {
                    this.tableLoading = false;
                })
            },
            modifyPlatformStatus(scope){

                this.$confirm('确认发起自动打款指令吗').then(() => {
                    api.get('/settlementPlatformBatches/modifyStatus', {batch_no: scope.row.batch_no}).then(data => {
                        this.getList();
                    });
                });
            },
        },
        created(){

            Object.assign(this.query, this.$route.params);
            this.getList();

        },

    }
</script>

<style scoped>

</style>