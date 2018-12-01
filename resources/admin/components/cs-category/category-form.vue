<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item v-if="form.parent_id > 0" prop="parent_id" label="所属分类">
                    <el-select v-model="form.parent_id">
                        <el-option v-for="item in topList" :key="item.id" :value="parseInt(item.id)" :label="item.cat_name"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="cat_name" label="商品分类名称">
                    <el-input v-model="form.cat_name"/>
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
    let defaultForm = {
        cat_name: '',
        status: 1,
        parent_id: 0,
    };
    export default {
        name: 'category-form',
        props: {
            data: Object,
            parent: Object,
            topList: Array,
        },
        computed:{

        },
        data(){
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    cat_name: [
                        {required: true, message: '名称不能为空'},
                        {max:4, message:'分类名称不能超过4个字'}
                    ]
                },
            }
        },
        methods: {
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data)
                }else {
                    this.form = deepCopy(defaultForm)
                    if(this.parent && this.parent.id > 0){
                        this.form.parent_id = this.parent.id
                    }
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                        setTimeout(() => {
                            this.$refs.form.resetFields();
                        }, 500)
                    }
                })

            }
        },
        created(){
            this.initForm();
        },
        watch: {
            data(){
                this.initForm();
            },
            parent(){
                this.initForm();
            }
        },
        components: {
        }
    }
</script>
<style scoped>

</style>
