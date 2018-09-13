<template>
    <!-- 会员管理操作页面 -->
    <div>
        <el-button type="text" @click="changeStatus">{{this.scope.row.isBind === 0 ? '':'解绑'}}</el-button>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "members-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {
                isEdit: false,
                showCreateAccountDialog: false,
            }
        },
        computed: {
        },
        methods: {

            changeStatus(){
                //let opt = '';
                this.$confirm(`确认要解绑[${this.scope.row.mobile}]吗?`).then( () => {
                    this.$emit('before-request')
                    api.post('/users/unBind', {id: this.scope.row.id}).then((data) => {
                        this.$emit('change', this.scope.$index, data)
                    }).finally(() => {
                        this.$emit('after-request')
                    })
                })
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