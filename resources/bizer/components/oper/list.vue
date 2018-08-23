<template>
    <page title="运营中心列表" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="createdAt" label="添加时间">
                <el-date-picker
                        v-model="query.createdAt"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
            </el-form-item>
            <el-form-item prop="name" label="运营中心名称">
                <el-input v-model="query.name" placeholder="请输入运营中心名称" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="principal" label="负责人">
                <el-input v-model="query.principal" placeholder="请输入负责人" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="contactNumber" label="联系电话">
                <el-input v-model="query.contactNumber" placeholder="请输入联系电话" clearable @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item prop="city" label="所在城市">
                <el-cascader 
                    change-on-select
                    clearable
                    filterable
                    :options="cityOptions"
                    :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                    v-model="query.city">
                </el-cascader>
            </el-form-item>
            <el-form-item prop="status" label="状态">
                <el-select v-model="query.status" class="w-100">
                    <el-option label="全部" value=""/>
                    <el-option label="正常" value="1"/>
                    <el-option label="申请中" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-button class="fr" type="primary" icon="el-icon-plus" @click="add">添加运营中心</el-button>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="运营中心名称"/>
            <el-table-column prop="name" label="负责人"/>
            <el-table-column prop="signboard_name" label="联系电话"/>
            <el-table-column prop="city" label="所在城市">
                <template slot-scope="scope">
                    <!-- <span> {{ scope.row.province }} </span> -->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="operBizMemberName" label="我的分成"/>
            <el-table-column prop="status" label="合作状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">申请中</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button type="text">查看商户</el-button>
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

        <el-dialog title="添加运营中心" :visible.sync="dialogFormVisible" width="30%">
            <el-form :model="form">
                <el-form-item label="运营中心名称" :label-width="formLabelWidth">
                    <el-select v-model="form.region" placeholder="请选择运营中心">
                        <el-option label="运营中心1" value="1"></el-option>
                        <el-option label="运营中心2" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="备注" :label-width="formLabelWidth">
                    <el-input type="textarea" v-model="form.desc" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogFormVisible = false">发送申请</el-button>
            </div>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data(){
            return {
                isLoading: false,
                cityOptions: [],
                query: {
                    createdAt: '',
                    name: '',
                    principal: '',
                    contactNumber: '',
                    status: '',
                    page: 1
                },
                list: [],
                total: 0,

                dialogFormVisible: false,
                form: {
                    region: '',
                    desc: ''
                },
                formLabelWidth: '120px'
            }
        },
        computed: {

        },
        methods: {
            search(){
                // this.query.page = 1;
                // this.getList();
            },
            getList(){
                // this.isLoading = true;
                // let params = {};
                // Object.assign(params, this.query);
                // api.get('/merchants', params).then(data => {
                //     this.query.page = params.page;
                //     this.isLoading = false;
                //     this.list = data.list;
                //     this.total = data.total;
                // })
            },
            add(){
                this.dialogFormVisible = true;
            },
        },
        created(){
            let _self = this;
            if (_self.$route.params){
                Object.assign(_self.query, _self.$route.params);
            }
            api.get('/api/bizer/area/tree?tier=2').then(data => {
                console.log(data.list)
                _self.cityOptions = data.list;
            });
            //this.getList();
        },
        components: {

        }
    }
</script>

<style scoped>

</style>
