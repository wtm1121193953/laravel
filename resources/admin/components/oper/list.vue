<template>
    <page title="运营中心管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加运营中心</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="运营中心名称"/>
            <el-table-column prop="contacter" label="负责人" />
            <el-table-column prop="tel" label="联系电话" />
            <el-table-column prop="status" label="合作状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常合作中</span>
                    <span v-else-if="scope.row.status === 2" class="c-warning">已冻结</span>
                    <span v-else-if="scope.row.status === 3" class="c-danger">停止合作</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="450px">
                <template slot-scope="scope">
                    <oper-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @refresh="getList"
                            @accountChanged="accountChanged"
                            @miniprogramChanged="miniprogramChanged"
                    />
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

        <el-dialog title="添加运营中心" :visible.sync="isAdd">
            <oper-form
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import OperItemOptions from './oper-item-options'
    import OperForm from './oper-form'

    export default {
        name: "oper-list",
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
                api.get('/opers', this.query).then(data => {
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
                api.post('/oper/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
                this.getList();
            },
            accountChanged(scope, account){
                let row = this.list[scope.$index];
                row.account = account;
                this.list.splice(scope.$index, 1, row);
                this.getList();
            },
            miniprogramChanged(scope, minprogram){
                let row = this.list[scope.$index];
                row.account = minprogram;
                this.list.splice(scope.$index, 1, row);
                this.getList();
            }
        },
        created(){
            this.getList();
        },
        components: {
            OperItemOptions,
            OperForm,
        }
    }
</script>

<style scoped>

</style>