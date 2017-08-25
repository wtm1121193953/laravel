<template>
    <!-- Vue file -->
    <div>


        <list
                :breadcrumb="{首页: function(){$menu.change('/')} }"
                title="权限列表"
                data-url="/rules"
                :searchForm="searchForm"
                addBtn="添加权限"
                exportBtn="导出"
                :columns="columns"
                :rowOptions="options"
                :edit="function(row){ return row.name != '用户管理'}"
                :disablePage="true"
                @add="add"
                @export="function(row){ $message.error('导出'); }"
        >
            <el-form slot="search" class="search-form fl" :inline="true" :model="searchForm">
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
            </el-form>
        </list>
    </div>
</template>
<script>
    import List from './list-page.vue'

    export default {
        data: function () {
            return {
                columns: {
                    ID: 'id',
                    权限名: 'name',
                    url: 'url',
                    状态: function(row){
                        return (row.status == 1
                            ? '<span class="c-green">正常</span>'
                            : '<span class="c-warning">禁用</span>');
                    },
                    创建时间: 'create_at',
                },
                options: {
                    修改状态: [function(row){
                        return bus.$message.error('sdafasdfkjfld修改状态');
                    }, function(row){
                        return row.name == '权限'
                    }]
                },
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
            editFunction(row){
                console.log(row);
                return row.name !== '权限'
            },
            add: function(){
                router.push('/boss/rule/add')
            }
        },
        created: function () {

        },
        components: {
            List
        }
    }
</script>