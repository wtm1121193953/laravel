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
                    :default-expanded-keys="expandedKeys"
                    node-key="id"
                    @node-expand="treeNodeExpand"
                    @node-collapse="treeNodeCollapse"
            >
                <span class="custom-tree-node" slot-scope="{ node, data }">
                    <img v-if="data.pid == 0 && data.icon" :src="data.icon" width="30px" height="30px">
                    <span :class="{'c-gray': data.status != 1}" class="name-list">{{data.name}} <template v-if="data.status != 1">(已禁用)</template></span>
                    <!--阻止事件冒泡, 方式点击按钮时树展开-->
                    <span @click.stop="() => {}">
                        <el-dropdown @command="(command) => categoryDropdownClicked(command, data)" trigger="click">
                            <el-button type="text">
                                操作 <i class="el-icon-arrow-down"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown" class="dropmenu">
                                <el-dropdown-item command="addChild" v-if="data.pid == 0">添加子类目</el-dropdown-item>
                                <el-dropdown-item command="edit">编辑</el-dropdown-item>
                                <el-dropdown-item command="changeStatus">{{data.status == 1 ? '禁用' : '启用'}}</el-dropdown-item>
                                <el-dropdown-item command="del">删除</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </span>
                </span>

            </el-tree>
        </el-col>
        <el-dialog :visible.sync="showForm" :title="formTitle" :close-on-click-modal="false" @close="cancel">
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
                        <el-form-item prop="icon" label="类目图标" v-if="form.pid == 0">
                            <image-upload v-model="form.icon" :limit="1"></image-upload>
                            <div class="tips">{{imageWidth}}px * {{imageHeight}}px</div>
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
        icon: '',
        status: 1,
        pid: 0
    }
    export default {
        name: "merchant-category-list",
        data(){
            return {
                imageWidth: 88,
                imageHeight: 88,
                isLoading: false,
                list: [],
                form: {
                    name: '',
                    icon: '',
                    status: 1,
                    pid: 0
                },
                formRules: {
                    name: [
                        {required: true, message: '类目名称不能为空'},
                        {max: 20, message: '类目名称不能超过20个字'},
                    ],
                    icon: [
                        {required: true, message: '类目图标不能为空'},
                    ],
                },
                showForm: false,
                formTitle: '添加类目',
                expandedKeys: [], // 类目树中展开的节点
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
                    case 'changeStatus':
                        this.changeStatus(data);
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
            changeStatus(data){
                let status = data.status == 1 ? 2 : 1;
                api.post('/merchant/category/changeStatus', {id: data.id, status: status}).then(data => {
                    this.getList();
                })
            },
            cancel(){
                this.showForm = false;
                this.form = deepCopy(defaultForm);
                this.$refs.form.resetFields();
            },
            save(){
                this.$refs.form.validate(valid => {
                    if (valid) {
                        if(this.form.id){
                            this.doEdit()
                        }else {
                            this.doAdd()
                        }
                    }
                })
            },
            doEdit(){
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
                this.$confirm(`确定要删除类目: ${data.name} 吗?`).then(() => {
                    api.post('/merchant/category/del', {id: data.id}).then(data => {
                        this.getList();
                    })
                })
            },
            treeNodeExpand(data){
                this.expandedKeys.push(data.id)
            },
            treeNodeCollapse(data){
                this.expandedKeys.splice(this.expandedKeys.indexOf(data.id), 1)
            },
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
        border-bottom: 1px solid #ebeef5;
    }
    .merchant-categories-tree>.el-tree-node:first-child .el-tree-node__content {
        border-top: 1px solid #ebeef5;
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
    .name-list {
        flex: auto;
        margin-left: 5px;
    }
</style>