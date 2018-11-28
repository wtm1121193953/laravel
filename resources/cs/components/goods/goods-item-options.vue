<template>
    <!-- 商品列表项操作 -->
    <div>
        <el-button v-if="parseInt(scope.row.audit_status) === 2" type="text" @click="fastEdit">编辑</el-button>
        <el-button v-else type="text" @click="edit">编辑</el-button>
        <el-button v-if="parseInt(scope.row.audit_status) === 2" type="text" @click="changeStatus">{{scope.row.status === 1 ? '下架' : '上架'}}</el-button>
        <el-button type="text" @click="sort">排序</el-button>
        <el-button type="text" @click="del">删除</el-button>

        <el-dialog title="编辑商品信息" :visible.sync="isEdit">
            <goods-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import GoodsForm from './goods-form'

    export default {
        name: "goods-item-options",
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
                router.push({
                    path: '/goods/edit',
                    query: {id: this.scope.row.id}
                });
                return false;
                this.isEdit = true;
            },
            fastEdit(){
                router.push({
                    path: '/goods/fast_edit',
                    query: {id: this.scope.row.id}
                });
                return false;
                this.isEdit = true;
            },
            doEdit(data){
                this.$emit('before-request')
                api.post('/goods/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                this.$emit('before-request')
                let opt = this.scope.row.status == 1 ? '下架' : '上架';
                api.post('/goods/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = data.status;
                    this.$message.success(opt + '商品成功')
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除商品 ${data.goods_name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/goods/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            saveOrder(row, type) {
                api.post('/goods/saveOrder', {id: row.id, type: type}).then(() => {
                    this.$emit('refresh');
                })
            },
            sort() {
                let data = this.scope.row;
                this.$prompt('请输入排序数值（越大越靠前）', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /^\d+$/,
                    inputErrorMessage: '请输入数字'
                }).then(({ value }) => {
                    this.$emit('before-request')
                    api.post('/goods/modifySort', {id: data.id,sort:value}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                }).catch(() => {

                });
            }
        },
        components: {
            GoodsForm
        }
    }
</script>

<style scoped>

</style>