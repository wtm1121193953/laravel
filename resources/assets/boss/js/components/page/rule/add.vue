<template>
    <page :breadcrumb="{首页: function(){$menu.change('/')}, 权限列表: '/boss/rule/list' }" title="添加权限">

        <el-col :span="16">
            <el-form ref="form" :model="form" :rules="formRules" label-width="130px">
                <el-form-item label="所属权限">
                    <el-select
                            v-model="form.pid"
                            class="w-200">
                        <el-option label="顶级权限" value=""></el-option>
                        <el-option v-for="item in parentRules" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="权限名称" prop="name">
                    <el-input v-model.trim="form.name" :placeholder=" form.pid ? '例：分组管理' : '例：权限管理' "></el-input>
                </el-form-item>

                <el-form-item :label="form.pid ? '路由地址' : '权限代号'" prop="name">
                    <el-input v-model.trim="form.url" :placeholder=" form.pid ? '例：/boss/group/list' : '例：authManage' "></el-input>
                </el-form-item>

                <el-form-item v-if="form.pid" label="权限接口列表">
                    <el-input
                            type="textarea"
                            :rows="10"
                            placeholder="多个接口地址之间以,分隔
示例 ：
/api/boss/test/getList ,
/api/boss/test/doAdd"
                            v-model="form.url_all"
                            class="w-400">
                    </el-input>
                </el-form-item>

                <el-form-item label="状态" prop="status">
                    <template>
                        <el-radio-group v-model="form.status">
                            <el-radio :label="1">启用</el-radio>
                            <el-radio :label="2">禁用</el-radio>
                        </el-radio-group>
                    </template>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="commitAdd('form')" :loading="isLoading">提交</el-button>
                    <el-button @click="$router.back()">返回</el-button>
                </el-form-item>
            </el-form>
        </el-col>

    </page>

</template>
<script>
    import api from '../../../../../js/api'
    import page from './components/page.vue'
    export default {
        data: function () {
            return {
                form: {
                    name   : '',
                    pid    : '',
                    status : 1,
                    url    : '',
                    url_all: ''
                },
                parentRules :[],
                formRules: {
                    name: [
                        { required: true, message: '请输入权限名称', trigger: 'blur' }
                    ],
                    url: [
                        { required: true, message: '请输入权限url', trigger: 'blur' }
                    ]
                },
                isLoading: false
            }
        },
        methods: {
            getTopRules(){
                let _self = this;
                api.get('/rule/getTopList', {}).then(res => {
                    api.handlerRes(res).then(data => {
                        _self.parentRules = data.list;
                    })
                }).catch((res) => {});
            }
        },
        created: function () {
            this.getTopRules();
        },
        components: {
            page
        }
    }
</script>