<template>
    <page title="类目管理" v-loading="isLoading">
        <el-col :span="8">
            <el-button type="primary" class="m-b-20 m-t-20" size="small" @click="add">
                <i class="el-icon-plus"></i> 添加类目
            </el-button>
            <el-tree
                    class="merchant-categories-tree"
                    :data="list"
                    :props="{label: 'name', children: 'sub'}"
                    :expand-on-click-node="false"
            >
                <span class="custom-tree-node" slot-scope="{ node, data }">
                    <span :class="{'c-gray': data.status != 1}">{{data.name}} <template v-if="data.status != 1">(已禁用)</template></span>
                    <span>
                        <el-dropdown @command="(command) => categoryDropdownClicked(command, data)" trigger="click">
                            <el-button type="text">
                                操作 <i class="el-icon-arrow-down"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown" class="dropmenu">
                                <el-dropdown-item command="addChild" v-if="data.pid == 0">添加子类目</el-dropdown-item>
                                <el-dropdown-item command="edit">编辑</el-dropdown-item>
                                <el-dropdown-item command="del">删除</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </span>
                </span>

            </el-tree>
        </el-col>
        <el-dialog :visible.sync="showForm" :title="formTitle">
            <el-row>
                <el-col :span="16">
                    <el-form ref="form" :model="form" :rules="formRules" label-width="120px" size="small">
                        <el-form-item prop="pid" label="所附父类目">
                            <el-select v-model="form.pid">
                                <el-option :value="0" label="顶级类目"/>
                                <el-option v-for="item in list" :key="item.id" v-if="item.status == 1" :value="item.id" :label="item.name"/>
                            </el-select>
                        </el-form-item>
                        <el-form-item prop="name" label="类目名称">
                            <el-input v-model="form.name"/>
                        </el-form-item>
                        <el-form-item prop="status" label="状态">
                            <el-radio-group v-model="form.status">
                                <el-radio :label="1">正常</el-radio>
                                <el-radio :label="2">禁用</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item>
                            <el-button @click="cancel">取消</el-button>
                            <el-button type="primary" @click="save">保存</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    let defaultForm = {
        name: '',
        status: 1,
        pid: 0
    }
    export default {
        name: "merchant-category-list",
        data(){
            return {
                isLoading: false,
                list: [],
                form: {
                    name: '',
                    status: 1,
                    pid: 0
                },
                formRules: {
                    name: [
                        {required: true, message: '类目名称不能为空'}
                    ]
                },
                showForm: false,
                formTitle: '添加类目'
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/merchant/category/tree').then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            categoryDropdownClicked(command, data){
                switch (command){
                    case 'addChild':
                        this.addChild(data)
                        break;
                    case 'edit':
                        this.edit(data)
                        break;
                    case 'del':
                        this.del(data);
                        break;
                }
            },
            addChild(data){
                this.add();
                this.form.pid = data.id;
            },
            add(){
                this.form = deepCopy(defaultForm);
                this.showForm = true;
                this.formTitle = "添加类目";
            },
            edit(data){
                this.form = deepCopy(data);
                this.showForm = true;
                this.formTitle = '编辑类目'
            },
            cancel(){
                this.showForm = false;
                this.$refs.form.resetFields();
            },
            save(){
                if(this.form.id){
                    this.doEdit()
                }else {
                    this.doAdd()
                }
            },
            doEdit(){
                console.log(JSON.parse(JSON.stringify(this.form)))
                api.post('/merchant/category/edit', this.form).then(data => {
                    this.showForm = false;
                    this.$refs.form.resetFields();
                    this.getList();
                })
            },
            doAdd(){
                api.post('/merchant/category/add', this.form).then(data => {
                    this.showForm = false;
                    this.$refs.form.resetFields();
                    this.getList();
                })
            },
            del(data){
                api.post('/merchant/category/del', {id: data.id}).then(data => {
                    this.getList();
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
        }
    }
</script>

<style>
    .merchant-categories-tree .el-tree-node__content {
        height: 40px;
    }
</style>

<style scoped>
    .custom-tree-node {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        line-height: 30px;
        padding-right: 8px;
    }
    .dropmenu {
        margin-top: 0;
    }
</style>