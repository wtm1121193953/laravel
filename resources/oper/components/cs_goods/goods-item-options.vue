<template>
    <!-- 商品列表项操作 -->
    <div>
        <el-button type="text" @click="check">查看</el-button>
        <el-button type="text" @click="audit">审核</el-button>
        <template>
            <el-dropdown trigger="click" @command="(command) => {fastAudit(scope, command)}">
                <el-button type="text">
                    快捷审核 <i class="el-icon-arrow-down"></i>
                </el-button>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="1">审核通过</el-dropdown-item>
                    <el-dropdown-item command="2">审核不通过</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </template>
        <el-dialog title="审核意见" :visible.sync="unAudit" :close-on-click-modal="false">
            <unaudit-message   @cancel="unAudit = false"  :data="scope.row"   @change="goodsChange"/>
        </el-dialog>
    </div>

</template>

<script>
    import api from '../../../assets/js/api'
    import UnauditMessage from './unaudit-message'

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
                unAudit:false,
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
            check() {
                router.push({
                    path: '/cs_goods/check',
                    query: {id: this.scope.row.id}
                });
            },
            audit() {
                router.push({
                    path: '/cs_goods/audit',
                    query: {id: this.scope.row.id}
                });
            },
            goodsChange() {
                this.$emit('refresh')
            },
            //type: 1-审核通过  2-审核不通过  3-审核不通过并打回到商户池
            fastAudit(scope, type){
                if(type==2 ||type==1){
                    scope.row.type = type;
                    this.unAudit = true;
                }

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
                api.post('/goods/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = data;
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
        },
        components: {
            UnauditMessage,
        }
    }
</script>

<style scoped>

</style>