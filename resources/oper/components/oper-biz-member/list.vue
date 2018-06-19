<template>
    <page title="我的业务员" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加业务员</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="created_at" label="加入时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 16)}}
                </template>
            </el-table-column>
            <el-table-column prop="name" label="姓名"/>
            <el-table-column prop="mobile" label="手机号"/>
            <el-table-column prop="code" label="推荐码"/>
            <el-table-column prop="activeMerchantNumber" label="激活商户（家）"/>
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

        <el-dialog title="添加业务员" :visible.sync="isAdd" width="500px">
            <operBizMember-form
                    ref="addForm"
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import OperBizMemberItemOptions from './operBizMember-item-options'
    import OperBizMemberForm from './operBizMember-form'

    export default {
        name: "operBizMember-list",
        data(){
            return {
                isAdd: false,
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
            getList(){
                api.get('/operBizMembers', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
            add(){
                this.isAdd = true;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/operBizMember/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                    this.$refs.addForm.resetForm();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
        },
        created(){
            this.getList();
        },
        components: {
            OperBizMemberItemOptions,
            OperBizMemberForm,
        }
    }
</script>

<style scoped>

</style>