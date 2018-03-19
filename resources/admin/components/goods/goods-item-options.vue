<template>
    <!-- 商品列表项操作 -->
    <div>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '下架' : '上架'}}</el-button>
        <el-button type="text" @click="stockManager">库存管理</el-button>
        <el-button type="text" @click="del">删除</el-button>

        <el-dialog title="编辑商品信息" :visible.sync="isEdit">
            <goods-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>

        <!-- 库存管理弹框 -->
        <el-dialog :title="`管理库存 ( ${scope.row.name} )`" :visible.sync="showManageStockDialog">
            <el-row>
                <el-col :span="15">
                    <el-form size="small" :rules="formRules" :model="form" label-width="120px">
                        <el-form-item label="总库存">
                            {{scope.row.total_count}}
                        </el-form-item>
                        <el-form-item label="总销量">
                            {{scope.row.sell_count}}
                        </el-form-item>
                        <el-form-item label="剩余与库存" prop="stock">
                            <el-input-number v-model="form.stock"/>
                        </el-form-item>
                        <el-form-item>
                            <el-button @click="showManageStockDialog = false">取消</el-button>
                            <el-button type="primary" @click="saveStock">确认</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import GoodsForm from './goods-form'
    import { mapGetters} from 'vuex'
    export default {
        name: "goods-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
                isEdit: false,
                showManageStockDialog: false,
                form: {
                    stock: this.scope.row.total_count - this.scope.row.sell_count
                },
                formRules: {
                    stock: [
                        {type: 'number', required: true, min: 0, message: '请填写正确的剩余库存数'}
                    ]
                }

            }
        },
        computed: {
            ...mapGetters('goods', [

            ]),
        },
        methods: {
            edit(){
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
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/goods/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除商品 ${data.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/goods/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            stockManager(){
                this.showManageStockDialog = true;
            },
            saveStock(){
                let data = this.scope.row;
                api.post('goods/changeStock', {id: data.id, stock: this.form.stock}).then((data) => {
                    this.showManageStockDialog = false;
                    this.$emit('change', this.scope.$index, data)
                })
            }
        },
        components: {
            GoodsForm
        }
    }
</script>

<style scoped>

</style>