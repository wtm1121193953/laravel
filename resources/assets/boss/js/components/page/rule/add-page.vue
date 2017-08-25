<template>
    <div>

        <!-- 页面路径 -->
        <el-breadcrumb class="p-b-10">
            <el-breadcrumb-item
                    v-for="(value, key) in breadcrumb"
                    :key="key"
                    @click.native="typeof value == 'function' ? value() : toPath(value)"
            >{{key}}</el-breadcrumb-item>
            <el-breadcrumb-item>{{title}}</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content">
            <div class="page-title">
                {{title}}
            </div>

            <el-form ref="form" :model="form" :rules="formRules" label-width="130px">

                <el-form-item
                        v-for="(field, key) in fields" :key="key"
                        :label="key"
                        :prop="getFieldName(field)"
                >
                    <el-select
                            v-if="getFieldType(field) == 'select'"
                            v-model="form[getFieldName(field)]"
                            :placeholder="getFieldPlaceholder(field)"
                            :rules="getFieldRules(field)"
                            class="w-200" clearable="">
                        <el-option v-for="item in field.select" :key="item.id" :label="item.name" :value="item.id"></el-option>
                    </el-select>

                </el-form-item>

                <el-form-item :label="form.pid ? '子权限名称' : '权限名称'" prop="name">
                    <el-input v-model.trim="form.name" class="h-40 w-200" :placeholder=" form.pid ? '例：分组管理' : '例：权限管理' "></el-input>
                </el-form-item>

                <el-form-item :label="form.pid ? '路由地址' : '权限代号'" prop="name">
                    <el-input v-model.trim="form.url" class="h-40 w-400" :placeholder=" form.pid ? '例：/boss/group/list' : '例：authManage' "></el-input>
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

        </el-row>

    </div>

</template>
<script>
    import api from '../../../../../js/api'

    /*
     *
     */
    export default {
        props: {
            breadcrumb: {type: Object, required: true},
            title: { type: String, required: true },
            url: {type: String, required: true},
            fields: {type: Array, required: true},
        },
        data() {
            return {
                isLoading: false,
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
                }
            }
        },
        computed: {
            url_all_for_post(){
                if (!this.form.url_all) return '';
                return this.form.url_all.toString().replace(/，/gm ,',').replace(/[\r\n]/igm,'').replace(/\s+/gm,'');
            }
        },
        methods: {
            getFieldName(field){
                return typeof field == 'string' ? field : field.name;
            },
            getFieldType(field){
                let type = typeof field == 'string' ? 'text' : field.type;
                return type || 'text';
            },
            getFieldPlaceholder(field){
                let placeholder = typeof field == 'string' ? '' : field.placeholder;
                return placeholder || '';
            },
            getFieldRules(field){

            },
            toPath(path){
                router.push(path);
            },
            commitAdd(form) {
                this.$refs[form].validate((valid) => {
                    if (valid) {
                        this.isLoading = !this.isLoading;
                        let postData = {
                            id     : this.form.id,
                            name   : this.form.name,
                            pid    : this.form.pid,
                            url    : this.form.url,
                            url_all: this.url_all_for_post,
                            status : this.form.status
                        };
                        api.post('/api/boss/auth/rule/create', postData).then(res => {
                            api.handlerRes(res).then(data => {
                                this.$message.success('添加成功');
                                this.$router.back();
                            })
                        }).catch((res) => {
                            this.isLoading = !this.isLoading
                        });
                    }
                })
            },
            getParentRuleList(){
                let _self = this;
                api.get('/api/boss/auth/rule/getParentRuleList', {}).then(res => {
                    api.handlerRes(res).then(data => {
                        _self.parentRules = data.list;
                    })
                }).catch((res) => {});
            }
        },
        created() {
            this.getParentRuleList();
        }
    }
</script>