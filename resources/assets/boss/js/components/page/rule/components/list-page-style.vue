<template>
    <!-- Vue file -->
    <page :breadcrumb="breadcrumb" :title="title">
        <!-- 搜索栏 -->
        <el-col>
            <template v-if="searchFormAvailable()">
                <slot name="search">

                </slot>
                <div class="el-form-item__content fl">
                    <el-button type="primary" size="small" @click="search">搜索</el-button>
                </div>
            </template>
            <!-- 右侧按钮 -->
            <div class="el-form-item fr">
                <el-button v-if="exportBtn" @click="$emit('export')">{{typeof exportBtn == 'string' ? exportBtn : '导出数据'}}</el-button>
                <el-button v-if="addBtn" type="success" @click="$emit('add')">{{typeof addBtn == 'string' ? addBtn : '添加数据'}}</el-button>
            </div>
        </el-col>

        <!-- 列表栏 -->
        <el-col>
            <el-table stripe :data="list" @selection-change="selectionChange" v-loading="loading">
                <el-table-column v-if="batchOptions" type="selection"></el-table-column>
                <el-table-column v-for="(name, key) in columns" :key="key" :label="key">
                    <template scope="scope">
                        <div v-html="typeof name == 'function' ? name(scope.row) : scope.row[name]"></div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" v-if="rowOptions || edit || del">
                    <template scope="scope">
                        <el-button v-for="(callback, key) in rowOptions"
                                   v-if="typeof callback == 'function'
                                        || (typeof callback[1] == 'function' && callback[1](scope.row))"
                                   :key="key"
                                   @click="typeof callback == 'function' ? callback(scope.row) : callback[0](scope.row)"
                                   type="text">{{key}}</el-button>
                        <el-button
                                v-if="typeof edit == 'function' ? edit(scope.row) : edit"
                                @click="$emit('edit', scope.row)" type="text">编辑</el-button>
                        <el-button
                                v-if="typeof del == 'function' ? del(scope.row) : del"
                                @click="$emit('del', scope.row)" type="text">删除</el-button>
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
                        v-if="!disablePage"
                        class="fr"
                        layout="total, prev, pager, next"
                        :current-page.sync="page"
                        @current-change="changePage"
                        :page-size="pageSize"
                        :total="total"
                ></el-pagination>
            </el-col>
        </el-col>
    </page>
</template>
<script>
    import page from './page.vue'
    import api from '../../../../../../js/api.js'

    /*
     * 事件列表 : add, export, search, edit, del
     * 属性列表
     *      breadcrumb: 页面面包屑, 不需要包含当前页面的标题
     *      title: 页面标题 必须
     *      dataUrl: 数据获取接口地址 必须
     *      searchForm: 搜索表单, 获取数据时会把搜索表单的数据加入到查询中
     *      columns: 要展示的数据列 必须
     *      addBtn: 添加数据按钮的文字, 布尔值或字符串, 值为true时按钮上的文字为默认的'添加数据', false不显示按钮 默认: false
     *      exportBtn: 导出按钮的文字, 布尔值或字符串, 值为true时按钮上的文字为默认的'导出数据', false不显示按钮 默认: false
     *      edit: 列表中是否有编辑选项  默认: 有
     *      del: 列表中是否有删除选项  默认: 有
     *      rowOptions: 列表每一行的其他操作项  可以为空
     *      disablePage: 是否禁用分页  默认不禁用
     *      batchOptions: 批量操作
     */
    export default {
        props: {
            breadcrumb: {type: Object},
            title: { type: String, required: true },
            dataUrl: {type: String, required: true},
            searchForm: {type: Object, default: null},
            columns: {type: Object, required: true},
            exportBtn: {type: [Boolean, String], default: false},
            addBtn: {type: [Boolean, String], default: false},
            edit: {type: [Boolean, Function], default: false},  // 是否有编辑选项
            del: {type: [Boolean, Function], default: false},  // 是否有编辑选项
            rowOptions: {type: Object},
            disablePage: {type: Boolean, default: false},
            batchOptions: {type: Object, default: null},
        },
        data: function () {
            return {
                loading: false,
                page: 1,
                pageSize: 15,
                total: 0,
                list: [],
                selectionList: [],
            }
        },
        methods: {
            searchFormAvailable(){
                return Object.keys(this.searchForm).length > 0
            },
            getList(){
                this.loading = true;
                let params = {};
                if(this.searchForm){
                    params = JSON.parse(JSON.stringify(this.searchForm));
                    params.page = this.page;
                }

                let self = this;
                api.get(this.dataUrl, params).then(res =>  {
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
        },
        components: {
            page
        }
    }
</script>