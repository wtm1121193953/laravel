<template>
    <page title="系统消息管理" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="app_type" label="">
                <el-form-item label="添加时间">
                    <el-date-picker
                            v-model="query.start_time"
                            type="date"
                            placeholder="选择开始日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd 00:00:00"
                    ></el-date-picker>
                    <span>—</span>
                    <el-date-picker
                            v-model="query.end_time"
                            type="date"
                            placeholder="选择结束日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd 23:59:59"
                            :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.start_time)}}"
                    ></el-date-picker>
                </el-form-item>
            </el-form-item>

            <el-form-item prop="content" label="">
                <el-input v-model="query.content" @keyup.enter.native="search" clearable placeholder="请输入消息内容关键字"/>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="add">+添加消息</el-button>
            </el-form-item>

        </el-form>

        <el-table :data="list" stripe>
            <el-table-column width="250" prop="created_at" label="添加时间"/>
            <el-table-column width="250" prop="title" label="标题"/>
            <el-table-column :show-overflow-tooltip="true" prop="content" label="内容">
                <template slot-scope="scope">
                    {{scope.row.content}}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button @click="showDetail(scope.row)">查看详情</el-button>
                </template>
            </el-table-column>
        </el-table>


        <el-dialog
                title="添加消息"
                :visible.sync="addDialog"
                width="60%"
                center
                :close-on-click-modal="false"
                :close-on-press-escape="false"
        >
            <el-form :model="addForm" ref="addForm" :rules="addFormRules" size="small" label-width="80px">
                <el-form-item prop="title" label="标题">
                    <el-input type="text" :rows="2" placeholder="请输入20个字以内的标题" v-model="addForm.title"/>
                </el-form-item>
                <el-form-item prop="object_type" label="角色">
                    <el-checkbox-group v-model="addForm.object_type" >
                        <el-checkbox :label="1" :key="1">用户端</el-checkbox>
                        <el-checkbox :label="2" :key="2">商户端</el-checkbox>
                        <el-checkbox :label="3" :key="3">业务员</el-checkbox>
                        <el-checkbox :label="4" :key="4">运营中心</el-checkbox>
                        <el-checkbox :label="5" :key="5">超市商户端</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item prop="content" label="内容">
                    <el-input type="textarea" :rows="20" placeholder="限2000个字" v-model="addForm.content"/>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="commit">确 定</el-button>
            </span>
        </el-dialog>

        <el-dialog title="" :visible.sync="isShowSystemDetail"  width="60%">
            <detail :scope="detailData"/>
        </el-dialog>
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
    import detail from './system-detail'

    export default {
        name: "systems-list",
        data(){
            return {
                detailData:{},
                isShowSystemDetail:false,
                isAdd: false,
                isLoading: false,
                query: {
                    page: 1,
                },
                addForm:{
                    object_type:[],
                    title:'',
                    content:''

                },
                list: [],
                total: 0,
                addDialog: false,

                addFormRules: {
                    object_type: [
                        {required: true, message: '批次类型不能为空'}
                    ],
                    content: [
                        {required: true, message: '内容不能为空'},
                        {max: 20000, message: '内容不能超过2000个字'},
                    ],
                    title: [
                        {required:true, message:'标题不能为空'},
                        {max:20, message:'标题不能超过20个字'}
                    ],
                }
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/message/systems', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            search(){
                this.getList();
            },
            cancel(){
                this.addDialog= false;
            },
            commit(){
                api.post('/message/addSystems', this.addForm ).then(data=>{
                    this.addDialog=false;
                    this.getList();
                });
                console.log('addForm',this.addForm);
            },
            add(){
                this.addDialog=true;
            },
            showDetail( data ){
                this.detailData = data;
                this.isShowSystemDetail = true;
                console.log(data)
            },

        },
        created(){
            this.getList();
        },
        components: {
            detail
        }
    }
</script>

<style>
    .el-tooltip__popper.is-dark{
        max-width:85%;
        display: none;
    }
    .el-tooltip__popper.is-dark{
        width:85%;
        display: none;
    }
</style>