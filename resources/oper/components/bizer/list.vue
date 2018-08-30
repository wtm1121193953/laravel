<template>
    <page title="我的业务员" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="加入时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 16)}}
                </template>
            </el-table-column>
            <el-table-column prop="bizerInfo.name" label="姓名"/>
            <el-table-column prop="bizerInfo.mobile" label="手机号"/>
            <el-table-column prop="divide" label="业务员分成"/>
            <el-table-column prop="activeNum" label="发展商户（家）"/>
            <el-table-column prop="auditNum" label="审核通过商户（家）"/>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === -1" class="c-danger">冻结</span>
                    <span v-else-if="scope.row.status === 2" class="c-warning">申请中</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="210px">
                <template slot-scope="scope">
                    <bizer-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @refresh="getList"/>
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
    import BizerItemOptions from './bizer-item-options'

    export default {
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
            }
        },
        computed: {

        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList();
            },
            itemChanged(){

            },
            getList(){
                let _self = this;
                api.get('/oper/bizers', this.query).then(data => {
                    console.log(data)
                    _self.list = data.list;
                    _self.total = data.total;
                }).catch((error) => {
                    _self.$message({
                      message: error.response && error.response.message ? error.response.message:'请求失败',
                      type: 'warning'
                    });
                }).finally(() => {

                })
            },
        },
        created(){
            this.getList();
        },
        components: {
            BizerItemOptions
        }
    }
</script>

<style scoped>

</style>