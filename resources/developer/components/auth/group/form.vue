<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form">
                <el-form-item prop="name" label="角色名">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item label="权限列表">
                    <el-tree
                            ref="tree"
                            :data="ruleTree"
                            show-checkbox
                            node-key="id"
                            :props="{label: 'name', children: 'sub'}"
                            default-expand-all>
                    </el-tree>
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

</template>
<script>
    import {mapState} from 'vuex'
    export default {
        name: 'group-form',
        props: {
            group: Object
        },
        data(){
            return {
                form: {
                    name: '',
                    status: 1,
                },
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                }
            }
        },
        computed: {
            ...mapState('auth', [
                'ruleTree',
                'rulesIdMapping',
                'rulePids'
            ])
        },
        methods: {
            initForm(){
                if(this.group){
                    this.form = deepCopy(this.group);
                    // 初始化时去掉有上级菜单,只保留叶子节点
                    let ruleIds = [];
                    this.group.rule_ids.split(',').forEach(id => {
                        if(this.rulePids.indexOf(parseInt(id)) < 0){
                            ruleIds.push(id)
                        }
                    });
                    this.$refs.tree.setCheckedKeys(ruleIds)
                }else {
                    this.form = {
                        name: '',
                        status: 1,
                    }
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        // 提交时将子菜单的父菜单加入权限列表
                        let ruleIds = [];
                        this.$refs.tree.getCheckedKeys().forEach(id => {
                            let rule = this.rulesIdMapping[id];
                            if(rule.pid > 0){
                                if(ruleIds.indexOf(rule.pid) < 0){
                                    ruleIds.push(rule.pid)
                                }
                            }
                            ruleIds.push(rule.id);
                        });
                        this.form.rule_ids = ruleIds.join(',');
                        this.$emit('save', deepCopy(this.form));
                        setTimeout(() => {
                            this.$refs.form.resetFields();
                            this.$refs.tree.setCheckedKeys([])
                        }, 500)
                    }
                })

            }
        },
        mounted(){
            this.initForm();
        },
        watch: {
            group(val){
                this.initForm();
            }
        }
    }
</script>
<style scoped>

</style>
