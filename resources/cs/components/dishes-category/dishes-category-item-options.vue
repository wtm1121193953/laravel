<template>
    <!-- xxxxxx列表项操作 -->
    <div>
        <el-button type="text" :disabled="isFirst" @click="saveOrder(scope.row, 'up')">上移</el-button>
        <el-button type="text" :disabled="isLast" @click="saveOrder(scope.row, 'down')">下移</el-button>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="changeStatus">{{parseInt(scope.row.status) === 1 ? '禁用' : '启用'}}</el-button>
        <el-button type="text" @click="del">删除</el-button>

        <el-dialog title="编辑分类信息" :visible.sync="isEdit" @close="dialogClose" width="25%">
            <dishes-category-form
                    ref="form"
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import DishesCategoryForm from './dishes-category-form'

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
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/dishes/category/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
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
                api.post('/dishes/category/saveOrder', {id: row.id, type: type}).then(() => {
                    this.$emit('refresh');
                })
            },
            dialogClose() {
                this.$refs.form.reset();
            }
        },
        components: {
            DishesCategoryForm
        },
    }
</script>

<style scoped>

</style>