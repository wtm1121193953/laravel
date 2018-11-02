<template>
    <page :title="title" :breadcrumbs="bread">
        <el-table :data="list" stripe v-loading="loading">
            <el-table-column prop="user_id" label="用户ID"></el-table-column>
            <el-table-column prop="user.mobile" label="用户手机号码"></el-table-column>
            <el-table-column prop="user.created_at" label="注册时间"></el-table-column>
        </el-table>
        <el-pagination
            class="fr m-t-20"
            layout="total, prev, pager, next"
            :current-page.sync="query.page"
            @current-change="getList"
            :page-size="15"
            :total="total"
        ></el-pagination>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "invite-list",
        data() {
            return {
                list: [],
                bread: {},
                title: '',
                query: {
                    page: 1,
                    originId: 0,
                    originType: 0,
                    timeType: '',
                    startDate: '',
                    endDate: '',
                },
                total: 0,
                loading: false,
            }
        },
        methods: {
            getList() {
                this.loading = true;
                api.get('/statistics/getInviteUserRecords', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.loading = false;
                })
            }
        },
        created() {
            if (!this.$route.query.originId) {
                this.$message.error('id不能为空');
                router.go(-1);
            }
            this.query.originId = this.$route.query.originId;
            this.query.originType = this.$route.query.originType;
            let name = this.$route.query.name;
            let timeType = this.$route.query.timeType;
            this.query.timeType = timeType;

            let text = '';
            let startDate = this.$route.query.startDate;
            let endDate = this.$route.query.endDate;
            if (timeType == 'all') {
                text = '全部';
            } else if (timeType == 'today') {
                text = '今日';
            } else if (timeType == 'yesterday') {
                text = '昨日';
            } else if (timeType == 'month') {
                text = '本月';
            } else if (timeType == 'lastMonth') {
                text = '上个月';
            } else {
                text = startDate + '-' + endDate;
                this.query.startDate = startDate;
                this.query.endDate = endDate;
            }

            if (this.$route.query.originType == 3) {
                this.title = name + '运营中心' + text + '会员明细';
                this.bread = {运营中心营销统计 : '/statistics/oper'};
            } else if (this.$route.query.originType == 2) {
                this.title = name + '商户' + text + '会员明细';
                this.bread = {商户营销统计 : '/statistics/merchant'};
            } else if (this.$route.query.originType == 1) {
                this.title = name + '用户' + text + '会员明细';
                this.bread = {用户营销统计 : '/statistics/user'};
            } else {
                this.$message.error('类型不能为空');
                router.go(-1);
            }

            this.getList();
        }
    }
</script>

<style scoped>

</style>