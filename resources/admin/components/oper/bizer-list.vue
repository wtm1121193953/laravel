<template>
    <page title="签约业务员" :breadcrumbs="{运营中心管理: '/opers'}">
        <el-form class="fl" inline :model="query" ref="query" size="small">
            <el-form-item prop="mobile" label="业务员手机号码">
                <el-input v-model="query.mobile" clearable placeholder="业务员手机号码"/>
            </el-form-item>
            <el-form-item prop="name" label="业务员昵称">
                <el-input v-model="query.name" clearable placeholder="业务员昵称"/>
            </el-form-item>
            <el-form-item>
                <el-button @click="search" type="primary"><i class="el-icon-search"></i>搜索</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column label="运营中心ID">
                <template slot-scope="scope">
                    {{query.operId}}
                </template>
            </el-table-column>
            <el-table-column prop="operInfo.name" label="运营中心名称"></el-table-column>
            <el-table-column prop="bizerInfo.name" label="业务员昵称"></el-table-column>
            <el-table-column prop="bizerInfo.mobile" label="业务员手机号码"></el-table-column>
            <el-table-column prop="bizerLog.created_at" label="签约时间"></el-table-column>
            <el-table-column prop="divide" label="分成比例"></el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="goToDivide(scope.row)">分成设置</el-button>
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


        <el-dialog title="业务员分成比例" center :visible.sync="showDivideDialog" width="20%">
            <el-input-number size="small" v-model="bizerDivideNumber" :precision="2" :max="100" style="width: 80%"/>%
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="showDivideDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="bizerDivide">确 定</el-button>
            </span>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "bizer-list",
        data() {
            return {
                list: [],
                total: 0,
                query: {
                    page: 1,
                    pageSize: 15,
                    mobile: '',
                    name: '',
                    operId: 0,
                },
                showDivideDialog: false,
                bizerDivideNumber: 0,
                row: {},
            }
        },
        methods: {
            getList() {
                api.get('/bizer/operBizer/list', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            goToDivide(row) {
                this.bizerDivideNumber = row.divide;
                this.row = row;
                this.showDivideDialog = true;
            },
            bizerDivide() {
                let param = {
                    operId: this.row.oper_id,
                    bizerId: this.row.bizer_id,
                    divide: this.bizerDivideNumber,
                }
                api.post('/oper/setBizerDivide', param).then(data => {
                    this.row.divide = data.divide;
                    this.showDivideDialog = false;
                    this.$message.success('分成设置成功');
                })
            }
        },
        created() {
            if (!this.$route.query.operId) {
                this.$message.error('运营中心ID不能为空')
            }
            this.query.operId = this.$route.query.operId;
            this.getList();
        }
    }
</script>

<style scoped>

</style>