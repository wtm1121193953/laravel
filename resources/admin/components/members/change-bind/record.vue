<template>
    <el-col>
        <el-form v-model="query" size="small" inline class="fl">
            <el-form-item label="运营中心名称">
                <el-input v-model="query.operName" placeholder="请输入运营中心名称" clearable/>
            </el-form-item>
            <el-form-item label="渠道名称">
                <el-input v-model="query.inviteChannelName" placeholder="请输入渠道名称" clearable/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜 索</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="换绑时间"/>
            <el-table-column prop="invite_channel_id" label="原渠道ID"></el-table-column>
            <el-table-column prop="invite_channel_name" label="渠道名称"/>
            <el-table-column prop="invite_channel_remark" label="备注"/>
            <el-table-column prop="invite_channel_oper_name" label="运营中心名称"/>
            <el-table-column prop="change_bind_number" label="换绑人数">
                <template slot-scope="scope">
                    <el-button type="text" @click="changeBindRecords(scope.row)">
                        {{scope.row.change_bind_number}}
                    </el-button>
                </template>
            </el-table-column>
            <el-table-column prop="new_invite_channel_id" label="新渠道ID"></el-table-column>
            <el-table-column prop="bind_mobile" label="新绑定帐号"/>
            <el-table-column prop="operator" label="操作人"/>
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
        name: "change-bind-record",
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
                api.get('users/getChangeBindRecordList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            changeBindRecords(data) {
                router.push(`/member/changeBindRecordList?id=${data.id}`);
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>