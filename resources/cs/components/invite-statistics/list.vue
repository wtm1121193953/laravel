<template>
    <page title="我的用户">
        <el-col>
            <el-form :model="query" class="fl" inline size="small">
                <el-form-item label="手机号码">
                    <el-input v-model="query.mobile" clearable placeholder="请输入手机号码" @keyup.enter.native="search"/>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="search">搜 索</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="success" @click="downloadExcel">导 出</el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-table :data="list" stripe @sort-change="sortChange">
            <el-table-column prop="created_at" label="注册日期"></el-table-column>
            <el-table-column prop="mobile" label="手机号码"></el-table-column>
            <el-table-column prop="wx_nick_name" label="微信昵称"></el-table-column>
            <el-table-column prop="order_count" label="下单次数" sortable="custom"></el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="query.pageSize"
                :total="total"
        ></el-pagination>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "member-list",
        data() {
            return {
                query: {
                    mobile: '',
                    page: 1,
                    pageSize: 15,
                    orderColumn: null,
                    orderType: null,
                },
                list: [],
                total: 0,
            }
        },
        methods: {
            getList() {
                api.get('/invite/statistics/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            downloadExcel() {
                location.href = '/api/cs/invite/statistics/downloadInviteRecordList?mobile=' + this.query.mobile;
            },
            sortChange(column) {
                this.query.orderColumn = column.prop;
                this.query.orderType = column.order;
                api.get('/invite/statistics/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>