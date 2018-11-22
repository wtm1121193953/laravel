<template>
    <!-- 商品列表项操作 -->
    <div>
        <el-button type="text" @click="check">查看</el-button>
        <el-button type="text" @click="audit">审核</el-button>

    </div>
</template>

<script>
    import api from '../../../assets/js/api'


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
        }
    }
</script>

<style scoped>

</style>