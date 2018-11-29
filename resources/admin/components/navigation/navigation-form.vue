<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="title" label="标题">
                    <el-input v-model="form.title"/>
                </el-form-item>
                <el-form-item prop="icon" label="图标">
                    <image-upload v-model="form.icon" :limit="1"></image-upload>
                </el-form-item>
                <el-form-item prop="type" label="类型">
                    <el-select v-model="form.type">
                        <el-option value="merchant_category" label="分类下的商户列表"></el-option>
                        <el-option value="merchant_category_all" label="全部分类"></el-option>
                        <el-option value="cs_index" label="大千超市首页"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item v-if="form.type == 'merchant_category'" prop="categoryId" label="商家类目">
                    <el-select v-model="form.categoryId" @change="selectChange">
                        <el-option v-for="item in topCategories" :key="item.id" :value="item.id" :label="item.name"></el-option>
                    </el-select>
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
    import api from '../../../assets/js/api'
    let defaultForm = {
        title: '',
        icon: '',
        type: 'merchant_category',
        categoryId: '',
    };
    export default {
        name: 'category-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    title: [
                        {required: true, message: '名称不能为空'}
                    ],
                    icon: [
                        {required: true, message: '图标不能为空'}
                    ],
                    type: [
                        {required: true, message: '类型不能为空'}
                    ],
                },
                topCategories: []
            }
        },
        methods: {
            selectChange(a){
                console.log(a, this.form)
            },
            initForm(){
                if(this.data){
                    if(this.data.type == 'merchant_category'){
                        this.data.categoryId = this.data.payload.category_id;
                    }
                    this.form = Object.assign({}, this.data);
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        if(this.form.type == 'merchant_category'){
                            if(!this.form.categoryId){
                                this.$message.error('分类不能为空')
                                return ;
                            }
                        }
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                        setTimeout(() => {
                            this.$refs.form.resetFields();
                        }, 500)
                    }
                })
            },
            getTopCategories(){
                api.get('/navigation/getAllTopMerchantCategories').then(data => {
                    this.topCategories = data.list;
                })
            }
        },
        created(){
            this.initForm();
            this.getTopCategories();
        },
        watch: {
            data(){
                this.initForm();
            },
        },
        components: {
        }
    }
</script>
<style scoped>

</style>
