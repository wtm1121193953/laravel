<template>
    <!-- 商户池列表项操作 -->
    <!-- todo 整理商户池操作项 -->
    <div>
        <el-button v-if="user.oper_id === scope.row.creator_oper_id" type="text" @click="edit">修改资料</el-button>
        <el-button type="text" @click="addFromPool">添加为我的商户</el-button>
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