<template>
    <!-- xxxxxx列表项操作 -->
    <div>
        <el-button type="text" :disabled="isFirst" @click="saveOrder(scope.row, 'up')">上移</el-button>
        <el-button type="text" :disabled="isLast" @click="saveOrder(scope.row, 'down')">下移</el-button>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '禁用' : '启用'}}</el-button>
        <el-button type="text" @click="del">删除</el-button>

        <el-dialog title="编辑单品信息" :visible.sync="isEdit">
            <dishes-goods-form
                    ref="form"
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import DishesGoodsForm from './dishes-goods-form'

    export default {
        name: "dishesGoods-item-options",
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
                api.post('/dishes/goods/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('refresh');
                    this.$refs.form.reset();
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/dishes/goods/changeStatus', {id: this.scope.row.id, status: status}).then(() => {
                    this.scope.row.status = status;
                    this.$emit('refresh');
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除 ${data.name} 单品吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/dishes/goods/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            saveOrder(row, type) {
                api.post('/dishes/goods/saveOrder', {id: row.id, type: type}).then(() => {
                    this.$emit('refresh');
                })
            },
        },
        components: {
            DishesGoodsForm
        }
    }
</script>

<style scoped>

</style>