<template>
    <page title="运营中心管理">
        <el-form class="fl" inline size="small">
            <el-form-item prop="name" label="">
                <el-input v-model="query.name" @keyup.enter.native="search" clearable placeholder="运营中心名称"/>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" clearable placeholder="请选择">
                    <el-option label="全部" value=""/>
                    <el-option label="正常合作中" value="1"/>
                    <el-option label="已冻结" value="2"/>
                    <el-option label="停止合作" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="tel" label="手机号码">
                <el-input v-model="query.tel" clearable @keyup.enter.native="search" placeholder="请输入手机号码"/>
            </el-form-item>
            <el-form-item label="支付到平台" prop="status">
                <el-select v-model="query.payToPlatform" clearable placeholder="请选择">
                    <el-option label="全部" value=""/>
                    <el-option label="支付到运营中心" value="3"/>
                    <el-option label="先切换到平台(平台不参与分成)" value="1"/>
                    <el-option label="切换到平台(平台按照合约参与分成)" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr" type="primary" @click="add">添加运营中心</el-button>
        <el-table :data="list" stripe v-loading="isLoading">
            <el-table-column prop="id" label="ID" width="100px"/>
            <el-table-column prop="name" label="运营中心名称" width="300px"/>
            <el-table-column prop="number" label="运营中心编码" />
            <el-table-column prop="contacter" label="负责人" />
            <el-table-column prop="tel" label="手机号码" />
            <el-table-column prop="status" label="合作状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常合作中</span>
                    <span v-else-if="scope.row.status === 2" class="c-warning">已冻结</span>
                    <span v-else-if="scope.row.status === 3" class="c-danger">停止合作</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_to_platform" label="支付到平台">
                <template slot-scope="scope">
                    <span v-if="scope.row.pay_to_platform === 0" >支付到运营中心</span>
                    <span v-else-if="scope.row.pay_to_platform === 1" >先切换到平台(平台不参与分成)</span>
                    <span v-else-if="scope.row.pay_to_platform === 2" >切换到平台(平台按照合约参与分成)</span>
                </template>
            </el-table-column>
            <!--
            <el-table-column prop="bindInfo" label="绑定TPS帐号">
                <template slot-scope="scope">
                    <span v-if="scope.row.bindInfo" class="title">{{scope.row.bindInfo.tps_account}}</span>
                    <span v-else>
                        <oper-tps-bind :scope="scope" @bound="(data) => {scope.row.bindInfo = data}"/>
                    </span>
                </template>
            </el-table-column>
            -->
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

    import OperTpsBind from './oper-tps-bind'
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
                    payToPlatform: '',
                    page: 1,
                    tel:''
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
                this.isLoading = true;
                api.get('/opers', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
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
            OperTpsBind,
            OperItemOptions,
            OperForm,
        }
    }
</script>

<style scoped>

</style>