<template>
    <!-- Vue file -->
    <div>
        <!-- 页面路径 -->
        <el-breadcrumb class="p-b-10">
            <el-breadcrumb-item @click.native="$menu.change('/demo/index')">首页</el-breadcrumb-item>
            <el-breadcrumb-item>用户管理</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content">
            <div class="page-title">
                用户列表
            </div>

            <!-- 搜索栏 -->
            <el-col>
                <el-form class="search-form fl" :inline="true" :model="searchForm">
                    <el-form-item>
                        <el-form-item>
                            <el-select v-model="searchForm.key" size="small" placeholder="请选择搜索项">
                                <el-option label="名称" value="name"></el-option>
                                <el-option label="描述" value="desc"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-input v-model="searchForm.keyword" size="small" placeholder="请输入关键字"></el-input>
                        </el-form-item>
                    </el-form-item>
                    <el-form-item label="状态：">
                        <el-select v-model="searchForm.status" size="small">
                            <el-option label="全部" value=""></el-option>
                            <el-option label="启用" value="1"></el-option>
                            <el-option label="禁用" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" size="small" @click="search">搜索</el-button>
                    </el-form-item>
                </el-form>
                <!-- 右侧添加按钮 -->
                <div class="fr">
                    <el-button @click="exportData">导出数据</el-button>
                    <el-button type="success" @click="add">添加数据</el-button>
                </div>
            </el-col>

            <!-- 列表栏 -->
            <el-col>
                <el-table stripe :data="list" @selection-change="selectionChange" v-loading="loading">
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column prop="username" label="用户名"></el-table-column>
                    <el-table-column prop="group_id" label="所属分组">
                        <template scope="scope">
                            {{scope.row.is_super == 1 ? '超级管理员' : scope.row.group_id}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="状态">
                        <template scope="scope">
                            <div :class="scope.row.status == 1 ? 'c-green' : 'c-warning'">
                            {{scope.row.status == 1 ? '正常' : '禁用'}}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_at" label="创建时间"></el-table-column>
                    <el-table-column label="操作">
                        <template scope="scope">
                            <el-button v-if="scope.row.is_super != 1" type="text" @click="changeStatus(scope.row)">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <!-- 表格底部 -->
                <el-col class="m-t-20">
                    <!-- 批量操作 -->
                    <!--<div class="fl">
                        <el-button type="success" size="small" @click="batchEnable">批量启用</el-button>
                        <el-button type="danger" size="small" @click="batchDisable">批量禁用</el-button>
                        <el-dropdown menu-align="start" @command="moreMenuClick">
                            <el-button size="small" type="primary">
                                更多 <i class="el-icon-more el-icon&#45;&#45;right"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="a">黄金糕</el-dropdown-item>
                                <el-dropdown-item command="b">狮子头</el-dropdown-item>
                                <el-dropdown-item command="c">螺蛳粉</el-dropdown-item>
                                <el-dropdown-item command="d" disabled>双皮奶</el-dropdown-item>
                                <el-dropdown-item command="e" divided>蚵仔煎</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </div>-->
                    <!-- 分页 -->
                    <el-pagination
                            class="fr"
                            layout="total, prev, pager, next"
                            :current-page.sync="searchForm.page"
                            @current-change="changePage"
                            :page-size="searchForm.pageSize"
                            :total="total"
                    ></el-pagination>
                </el-col>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    import api from '../../../../../js/api.js'

    export default {
        data: function () {
            return {
                searchForm: {
                    key: '',
                    keyword: '',
                    status: '',
                    page: 1
                },
                loading: false,
                total: 0,
                list: [],
                selectionList: [],
            }
        },
        methods: {
            exportData(){
                this.$message.success('导出数据成功');
            },
            add(){
                this.$message.success('添加数据成功');
            },
            getList(){
                this.loading = true;
                let self = this;
                api.get('users', this.searchForm).then(res =>  {
                    self.loading = false;
                    api.handlerRes(res).then(data => {
                        self.list = data.list;
                        self.total = data.total;
                    })
                });
            },
            // 搜索列表
            search(){
                this.getList();
            },
            // 翻页
            changePage(){
                this.getList();
            },
            // 改变状态
            changeStatus(row){
                row.status = row.status == 1 ? 2 : 1;
            },
            // 列表项选中事件
            selectionChange(list){
                this.selectionList = list;
            },
            // 批量启用
            batchEnable(){
                let _self = this;
                if(_self.selectionList.length <= 0){
                    this.$message.warning('请先选择项目');
                    return false;
                }
                this.$confirm('确定要启用你所选择的项目吗? ').then(() => {
                    _self.selectionList.forEach(item => {
                        item.status = 1;
                    });
                });
            },
            // 批量禁用
            batchDisable(){
                let _self = this;

                if(_self.selectionList.length <= 0){
                    this.$message.warning('请先选择项目');
                    return false;
                }
                this.$confirm('确定要禁用你所选择的项目吗? ').then(() => {
                    _self.selectionList.forEach(item => {
                        item.status = 2;
                    });
                });
            },
            moreMenuClick(options){
                this.$message.success('您点击了 ' + options + ' 选项');
            }
        },
        created: function () {
            this.getList();
        }
    }
</script>