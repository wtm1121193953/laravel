<template>
    <el-col>
        <el-form v-model="query" size="small" inline class="fl">
            <el-form-item label="运营中心名称">
                <el-input v-model="query.operName" clearable/>
            </el-form-item>
            <el-form-item label="渠道名称">
                <el-input v-model="query.inviteChannelName" clearable/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜 索</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="operName" label="运营中心名称"/>
            <el-table-column prop="name" label="渠道名称"/>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="invite_user_records_count" label="注册人数">
                <template slot-scope="scope">
                    <el-button type="text" @click="inviteRecords(scope.row)">
                        {{scope.row.invite_user_records_count}}
                    </el-button>
                </template>
            </el-table-column>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.invite_user_records_count > 0" type="text" @click="changeBind(scope.row)">换 绑</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="query.pageSize"
                :total="total"/>
    </el-col>
</template>

<script>
    import api from '../../../../assets/js/api';

    export default {
        name: "change-bind-list",
        data() {
            return {
                query: {
                    operName: '',
                    inviteChannelName: '',
                    page: 1,
                    pageSize: 15,
                },
                list: [],
                total: 0,
                tableLoading: false,
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('users/getChangeBindList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            changeBind(data) {
                router.push(`/member/changeBindList?id=${data.id}`);
            },
            inviteRecords(data) {
                router.push(`/member/inviteUsersList?id=${data.id}&name=${data.name}`);
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>