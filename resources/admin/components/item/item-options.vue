<template>
    <!-- 商品列表项操作 -->
    <div>
        <el-button type="text" @click="edit(scope)">编辑</el-button>
        <el-button type="text" @click="changeStatus(scope)">{{scope.row.status === 1 ? '下架' : '上架'}}</el-button>
        <el-button type="text" @click="leftCountManager(scope)">库存管理</el-button>
        <el-button type="text" @click="del(scope)">删除</el-button>

        <el-dialog title="编辑商品信息" :visible.sync="isEdit">
            <item-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>

        <!-- 库存管理弹框 -->
        <el-dialog :title="`管理库存 ( ${scope.row.name} )`" :visible.sync="showManageLeftCountDialog">
            <el-row>
                <el-col :span="15">
                    <el-form size="small" :rules="formRules" :model="form" label-width="120px">
                        <el-form-item label="总库存">
                            {{scope.row.total_count}}
                        </el-form-item>
                        <el-form-item label="总销量">
                            {{scope.row.sell_count}}
                        </el-form-item>
                        <el-form-item label="剩余与库存" prop="leftCount">
                            <el-input-number v-model="form.leftCount"/>
                        </el-form-item>
                        <el-form-item>
                            <el-button @click="showManageLeftCountDialog = false">取消</el-button>
                            <el-button type="primary" @click="saveLeftCount">确认</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import ItemForm from './item-form'
    import { mapGetters} from 'vuex'
    export default {
        name: "item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
                isEdit: false,
                showManageLeftCountDialog: false,
                form: {
                    leftCount: this.scope.row.total_count - this.scope.row.sell_count
                },
                formRules: {
                    leftCount: [
                        {type: 'number', required: true, min: 0, message: '请填写正确的剩余库存数'}
                    ]
                }

            }
        },
        computed: {
            ...mapGetters('items', [

            ]),
        },
        methods: {
            edit(){
                this.isEdit = true;
            },
            doEdit(data){
                this.$emit('before-request')
                api.post('/item/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/item/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
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
                    api.post('/item/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
            leftCountManager(){
                this.showManageLeftCountDialog = true;
            },
            saveLeftCount(){
                let data = this.scope.row;
                api.post('item/changeLeftCount', {id: data.id, leftCount: this.form.leftCount}).then((data) => {
                    this.showManageLeftCountDialog = false;
                    this.$emit('change', this.scope.$index, data)
                })
            }
        },
        components: {
            ItemForm
        }
    }
</script>

<style scoped>

</style>