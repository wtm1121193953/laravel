<template>
    <page title="我的业务员" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="加入时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 16)}}
                </template>
            </el-table-column>
            <el-table-column prop="name" label="姓名"/>
            <el-table-column prop="mobile" label="手机号"/>
            <el-table-column prop="dividedInto" label="业务员分成"/>
            <el-table-column prop="activeMerchantNumber" label="发展商户（家）"/>
            <el-table-column prop="auditMerchantNumber" label="审核通过商户（家）"/>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <operBizMember-item-options
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
                this.getList()
            },
            getList(){
                // api.get('/operBizMembers', this.query).then(data => {
                //     this.list = data.list;
                //     this.total = data.total;
                // })
            },
            itemChanged(index, data){
                // this.list.splice(index, 1, data);
                //     router.replace({
                //         path: '/refresh',
                //         query: {
                //             name: 'OperBizMemberList',
                //             key: '/operBizMembers'
                //         }
                //     })
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