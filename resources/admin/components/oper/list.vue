<template>
    <page title="运营中心管理" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="name" label="">
                <el-input v-model="query.name" @keyup.enter.native="search" placeholder="运营中心名称"/>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" placeholder="请选择">
                    <el-option label="全部" value=""/>
                    <el-option label="正常合作中" value="1"/>
                    <el-option label="已冻结" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr" type="primary" @click="add">添加运营中心</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="运营中心名称" width="300px"/>
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
            <el-table-column label="操作" width="550px">
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
                isLoading: false,
                query: {
                    name: '',
                    status: '',
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
                router.push('/oper/add')
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