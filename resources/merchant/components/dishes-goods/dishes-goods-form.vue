<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="商品名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="marketPrice" label="市场价">
                    <el-input-number v-model="form.marketPrice"></el-input-number>
                </el-form-item>
                <el-form-item prop="salePrice" label="销售价">
                    <el-input-number v-model="form.salePrice"></el-input-number>
                </el-form-item>
                <el-form-item prop="categoryId" label="类别">
                    <el-select v-model="form.categoryId" placeholder="请选择">
                        <el-option
                            v-for="item in categories"
                            :key="item.id"
                            :label="item.name"
                            :value="item.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item prop="logo" label="商品logo图">
                    <image-upload v-model="form.logo"></image-upload>
                </el-form-item>
                <el-form-item prop="detailImage" label="商品详情图">
                    <image-upload v-model="form.detailImage"></image-upload>
                </el-form-item>
                <el-form-item prop="intro" label="商品简介">
                    <el-input type="textarea" v-model="form.intro"></el-input>
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
        name: '',
        marketPrice: 0,
        salePrice: 0,
        categoryId: '',
        logo: '',
        detailImage: '',
        intro: '',
        status: 1,
    };
    export default {
        name: 'dishesGoods-form',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                form: deepCopy(defaultForm),
                categories: [],
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                },
            }
        },
        methods: {
            initForm(){
                if(this.data.id){
                    this.form = deepCopy(this.data)
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
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
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
            }
        },
        components: {
        }
    }
</script>
<style scoped>

</style>
