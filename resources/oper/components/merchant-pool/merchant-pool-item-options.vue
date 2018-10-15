<template>
    <!-- 商户池列表项操作 -->
    <div>
        <el-button v-if="user.oper_id === scope.row.creator_oper_id" type="text" @click="edit">编辑</el-button>
        <el-button type="text" @click="addFromPool">激活商户</el-button>
        <el-button v-if="user.oper_id === scope.row.creator_oper_id" type="text" @click="del">删除</el-button>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import {mapState} from 'vuex'
    import MerchantForm from './merchant-pool-form'

    export default {
        name: "merchant-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
            }
        },
        computed: {
            ...mapState([
                'user',
            ])
        },
        methods: {
            edit(){
                router.push({
                    path: '/merchant/pool/edit',
                    query: {
                        id: this.scope.row.id,
                    }
                })
            },
            del(){
                this.$confirm(`确定删除吗?`).then(() => {
                    api.post('/merchant/pool/del', {id: this.scope.row.id}).then(data => {
                        this.$alert(' 操作成功');
                        this.$emit('refresh')
                    })
                });
            },
            addFromPool(){
                router.push({
                    path: '/merchant/add-from-merchant-pool',
                    query: {
                        id: this.scope.row.id,
                    }
                })
            }
        },
        components: {
            MerchantForm
        }
    }
</script>

<style scoped>

</style>