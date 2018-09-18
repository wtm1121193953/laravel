<template>
    <el-form :model="form" ref="form" :rules="formRules" label-width="100px">
        <el-form-item prop="keyword" label="关键词">
            <el-input v-model="form.keyword" style="width: 350px"/>
        </el-form-item>
        <el-form-item prop="category" label="适用分类" required>
            <el-checkbox-group v-model="form.category">
                <el-checkbox v-for="item in filterKeywordCategoryList" :key="item.categoryNumber" :label="item.categoryNumber">{{item.categoryName}}</el-checkbox>
            </el-checkbox-group>
        </el-form-item>
        <el-form-item prop="status" label="状态">
            <el-radio v-model="form.status" :label="1">正常</el-radio>
            <el-radio v-model="form.status" :label="2">禁用</el-radio>
        </el-form-item>
        <el-form-item>
            <el-button @click="cancel">返 回</el-button>
            <el-button type="primary" @click="save">保 存</el-button>
        </el-form-item>
    </el-form>
</template>

<script>
    import api from '../../../../assets/js/api'
    import {mapState} from 'vuex'

    export default {
        name: "filter-keyword-form",
        props: {
            editData: {
                type: Object,
                default: {},
            }
        },
        data() {
            let categoryValidate = (rule, value, callback) => {
                if (value.length <= 0) {
                    return callback(new Error('适用分类不能为空'));
                } else {
                    callback();
                }
            };

            return {
                form: {
                    keyword: '',
                    status: 1,
                    category: [],
                },

                formRules: {
                    keyword: [
                        {required: true, message: '关键词不能为空'},
                        {max: 20, message: '关键词不能超过20个字'},
                    ],
                    category: [
                        {validator: categoryValidate}
                    ],
                }
            }
        },
        computed: {
            ...mapState([
                'filterKeywordCategoryList'
            ])
        },
        methods: {
            cancel() {
                this.$emit('cancel');
            },
            save() {
                let self = this;
                this.$refs.form.validate(valid => {
                    if (valid){
                        if (!self.form.id) {
                            api.post('setting/filterKeyword/add', self.form).then(data => {
                                self.$message.success('添加成功');
                                self.$emit('addOrEditSuccess');
                            })
                        } else {
                            api.post('setting/filterKeyword/edit', self.form).then(data => {
                                self.$message.success('编辑成功');
                                self.$emit('addOrEditSuccess');
                            })
                        }
                    }
                })
            },
            resetForm() {
                this.$refs.form.resetFields();
            },
            init() {
                let data = this.editData;
                if (data.id) {
                    this.form.id = data.id;
                    this.form.keyword = data.keyword;
                    this.form.category = data.category;
                    this.form.status = parseInt(data.status);
                }else {
                    this.form.id = '';
                    this.form.keyword = '';
                    this.form.category = [];
                    this.form.status = 1;
                }
            }
        },
        created() {
            this.init();
        },
        watch: {
            editData() {
                this.init();
            }
        }
    }
</script>

<style scoped>

</style>