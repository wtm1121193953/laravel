<template>
    <!-- 会员管理操作页面 -->
    <div>
        <el-button type="text" @click="detail">查看</el-button>
        <el-button type="text" @click="edit">审核</el-button>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "identity-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
            }
        },
        computed: {
        },
        methods: {

            edit(){
                router.push({
                    path: '/member/identity/edit',
                    query: {id: this.scope.row.id}
                });
            },
            detail() {
                router.push({
                    path: '/member/identity/detail',
                    query: {id: this.scope.row.id}
                });
            },
        },
        created(){
        },
        components: {
        }
    }
</script>

<style scoped>

</style>