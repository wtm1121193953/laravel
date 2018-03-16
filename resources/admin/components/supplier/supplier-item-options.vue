<template>
    <!-- xxxxxxx列表项操作 -->
    <div>
        <el-button type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '禁用' : '启用'}}</el-button>
        <el-button type="text" @click="del">删除</el-button>

        <el-dialog title="编辑xxxxxxx信息" :visible.sync="isEdit">
            <supplier-form
                    :data="scope.row"
                    @cancel="isEdit = false"
                    @save="doEdit"/>
        </el-dialog>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import SupplierForm from './supplier-form'
    import { mapState, mapGetters} from 'vuex'
    export default {
        name: "supplier-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
                isEdit: false,
            }
        },
        computed: {
            ...mapState('supplier', [
                // some state mapping here
            ]),
            ...mapGetters('supplier', [
                // some getter mapping here
            ]),
        },
        methods: {
            edit(){
                this.isEdit = true;
            },
            doEdit(data){
                this.$emit('before-request')
                api.post('/supplier/edit', data).then((data) => {
                    this.isEdit = false;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/supplier/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            del(){
                let data = this.scope.row;
                this.$confirm(`确定要删除xxxxxxx ${data.name} 吗? `, '温馨提示', {type: 'warning'}).then(() => {
                    this.$emit('before-request')
                    api.post('/supplier/del', {id: data.id}).then(() => {
                        this.$emit('refresh')
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
            },
        },
        components: {
            SupplierForm
        }
    }
</script>

<style scoped>

</style>