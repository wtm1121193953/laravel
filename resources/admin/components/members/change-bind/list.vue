<template>
    <el-col>
        <el-form v-model="query" size="small" inline class="fl">
            <el-form-item label="运营中心名称">
                <el-input v-model="query.operName" placeholder="请输入运营中心名称" clearable/>
            </el-form-item>
            <el-form-item label="渠道名称">
                <el-input v-model="query.inviteChannelName" placeholder="请输入渠道名称" clearable/>
            </el-form-item>
            <el-form-item label="推广人类型" v-if="hasRule('/api/admin/users/changeBindAdmin')">
                <el-select v-model="query.originType" class="w-100" clearable>
                    <el-option label="用户" :value="1"></el-option>
                    <el-option label="商户" :value="2"></el-option>
                    <el-option label="运营中心" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="推广人用户手机号码" v-if="query.originType == 1 && hasRule('/api/admin/users/changeBindAdmin')">
                <el-input v-model="query.mobile" clearable/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜 索</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="id" label="渠道ID"></el-table-column>
            <el-table-column prop="operName" label="运营中心名称" width="350px"/>
            <el-table-column prop="name" label="渠道名称" width="350px"/>
            <el-table-column prop="origin_id" label="推广人ID"></el-table-column>
            <el-table-column prop="origin_type" label="推广人类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.origin_type == 1">用户</span>
                    <span v-else-if="scope.row.origin_type == 2">商户</span>
                    <span v-else-if="scope.row.origin_type == 3">运营中心</span>
                    <span v-else>其他({{scope.row.origin_type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间" width="200px"/>
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
                    originType: 3,
                    mobile: '',
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