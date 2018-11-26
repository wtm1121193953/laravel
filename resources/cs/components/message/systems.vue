<template>
    <page title="公告" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item prop="app_type" label="">
                <el-form-item label="添加时间">
                    <el-date-picker
                            v-model="query.start_time"
                            type="date"
                            placeholder="选择开始日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd"
                    ></el-date-picker>
                    <span>—</span>
                    <el-date-picker
                            v-model="query.end_time"
                            type="date"
                            placeholder="选择结束日期"
                            format="yyyy 年 MM 月 dd 日"
                            value-format="yyyy-MM-dd"
                            :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startDate) - 8.64e7}}"
                    ></el-date-picker>
                </el-form-item>
            </el-form-item>

            <el-form-item prop="content" label="">
                <el-input v-model="query.content" @keyup.enter.native="search" clearable placeholder="请输入消息内容关键字"/>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
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
                    object_type:3
                },
                list: [],
                total: 0,
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
            commit(){
                api.post('/message/addSystems', this.addForm ).then(data=>{
                    this.getList();
                });
                console.log('addForm',this.addForm);
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