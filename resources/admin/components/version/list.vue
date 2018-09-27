<template>
    <page title="APP版本管理">
        <el-form class="fl" inline size="small">
            <el-form-item label="" prop="app_type">
                <el-select v-model="query.app_type">
                    <el-option label="Android" :value="1"/>
                    <el-option label="IOS" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="app_name" label="">
                <el-input v-model="query.app_name" @keyup.enter.native="search" clearable placeholder="应用名称"/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr" type="primary" @click="add">添加版本</el-button>
        <el-table :data="list" stripe v-loading="isLoading">
            <el-table-column prop="id" label="版本ID" width="100px"/>
            <el-table-column prop="app_name" label="应用名称" />
            <el-table-column prop="app_type" label="应用类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.app_type === 1">IOS</span>
                    <span v-else-if="scope.row.app_type === 2">Android</span>
                </template>
            </el-table-column>
            <el-table-column prop="app_tag" label="版本标签" />
            <el-table-column prop="app_num" label="版本号" />
            <el-table-column prop="version_num" label="版本序号" />
            <el-table-column prop="created_at" label="发布时间" />
            <el-table-column prop="status" label="发布状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">暂不发布</span>
                    <span v-else-if="scope.row.status === 2" class="c-warning">已发布</span>
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

    import VersionForm from './version-form'

    export default {
        name: "version-list",
        data(){
            return {
                isLoading: false,
                query: {
                    app_name: '',
                    app_type: 1
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
                api.get('/versions', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                router.push('/version/add')
            }
        },
        created(){
            this.getList();
        },
        components: {
            VersionForm
        }
    }
</script>

<style scoped>

</style>