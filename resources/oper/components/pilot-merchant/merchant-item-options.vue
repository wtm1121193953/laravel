<template>
    <!-- 商户列表项操作 -->
    <div>
        <el-button  type="text" @click="showMearchant(scope)">查看</el-button>
        <el-button v-if="parseInt(scope.row.audit_status) === 0 || parseInt(scope.row.audit_status) === 2" type="text" @click="edit">编辑</el-button>
        <el-button v-if="parseInt(scope.row.audit_status) !== 0 && parseInt(scope.row.audit_status) !== 2" type="text" @click="changeStatus">{{parseInt(scope.row.status) === 1 ? '冻结' : '解冻'}}</el-button>
        <el-button type="text" @click="complementInfo(scope.row)">补全资料</el-button>

    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantForm from './form'

    export default {
        name: "pilot-merchant-item-options",
        props: {
            scope: {type: Object, required: true},
            query: {type: Object}
        },
        data(){
            return {

            }
        },
        computed: {

        },
        methods: {
            edit(){
                let self = this;
                router.push({
                    path: '/merchant/pilot/edit',
                    name: 'PilotMerchantEdit',
                    query: {
                        id: this.scope.row.id,
                    },
                    params: self.query,
                })
            },

            showMearchant(scope){
                router.push({
                    path: '/merchant/pilot/detail',
                    query: {id: scope.row.id},
                })
                return false;

            },
            changeStatus(){
                let status = this.scope.row.status === 1 ? 2 : 1;
                this.$emit('before-request')
                api.post('/merchant/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                    this.scope.row.status = status;
                    this.$emit('change', this.scope.$index, data)
                }).finally(() => {
                    this.$emit('after-request')
                })
            },
            complementInfo(row) {
                router.push({
                    path: '/merchant/edit',
                    name: 'MerchantEdit',
                    query: {
                        id: row.id,
                        type: 'merchant-list',
                        isPilot: true,
                    },
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