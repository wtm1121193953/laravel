<template>
    <!-- 商品分类列表项操作 -->
    <div>
        <el-button type="text" @click="edit">编辑</el-button>

        <el-dialog title="编辑商品分类信息" :visible.sync="isEdit">
            <category-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import CategoryForm from './navigation-form'

    export default {
        name: "category-item-options",
        props: {
            scope: {type: Object, required: true},
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
                api.post('/navigation/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除商品分类 ${data.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/category/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
        },
        components: {
            CategoryForm
        }
    }
</script>

<style scoped>

</style>