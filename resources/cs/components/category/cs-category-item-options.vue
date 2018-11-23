<template>
    <!-- xxxxxx列表项操作 -->
    <div>
        <el-button type="text" :disabled="isFirst" @click="saveOrder(scope.row, 'up')">上移</el-button>
        <el-button type="text" :disabled="isLast" @click="saveOrder(scope.row, 'down')">下移</el-button>
        <el-button type="text" @click="changeStatus">{{parseInt(scope.row.status) === 1 ? '下架' : '上架'}}</el-button>
        <el-button v-if="parseInt(scope.row.cs_category_level) === 1" type="text" @click="subCat">查看子分类</el-button>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import DishesCategoryForm from './cs-category-form'

    export default {
            name: "dishes-category-item-options",
        props: {
            scope: {type: Object, required: true},
            isFirst: {type: Boolean, default: false},
            isLast: {type: Boolean, default: false},
        },
        data(){
            return {
                isEdit: false,
            }
        },
        computed: {

        },
        methods: {
            edit(){
                this.isEdit = true;
            },
            doEdit(data){
                this.$emit('before-request')
                api.post('dishes/category/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                this.$emit('before-request')
                console.log(this.scope)
                let data = this.scope.row;
                if(data.status == 1){
                    let message = `下架顶级分类将会下架该分类下的所有子分类, 以及子分类下的所有商品, 确定下架分类 ${data.cs_cat_name} 吗?`
                    if(data.cs_category_parent_id > 0){
                        message = `下架分类将会该分类下的所有商品, 确定下架分类 ${data.cs_cat_name} 吗?`
                    }
                    this.$confirm(message).then(() => {
                        api.post('/category/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                            this.scope.row.status = data;
                        }).finally(() => {
                            this.$emit('after-request')
                        })
                    })
                }else {
                    api.post('/category/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                        this.scope.row.status = data;
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                }

            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除 ${data.name} 分类吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/dishes/category/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            saveOrder(row, type) {
                api.post('/category/changeSort', {platform_category_id: row.platform_category_id, type: type}).then(() => {
                    this.$emit('refresh');
                })
            },
            dialogClose() {
                this.$refs.form.reset();
            },
            subCat() {
                router.push({
                    path: '/subCategories',
                    query: {
                        cs_category_parent_id: this.scope.row.platform_category_id,
                        cs_category_parent_name: this.scope.row.cs_cat_name,
                    }
                });
            }
        },
        components: {
            DishesCategoryForm
        },
    }
</script>

<style scoped>

</style>