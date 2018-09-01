<template>
    <!-- 会员管理操作页面 -->
    <div>
        <el-button type="text" @click="detail">查看</el-button>
        <el-button type="text" @click="edit">审核</el-button>
        <el-dropdown @command="quickAudit">
            <span class="el-dropdown-link">
                <el-button type="text">快捷审核<i class="el-icon-arrow-down el-icon--right"></i></el-button>
            </span>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="1">审核通过</el-dropdown-item>
                <el-dropdown-item command="2">审核不通过</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
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
            quickAudit(type){
                if(type == 1){
                    this.$confirm('确认审核通过吗').then(() => {
                        this.loading = true;
                        let data = {id:this.scope.row.id,status:2}
                        api.post('/member/identity_do', data).then((data) => {
                            this.$emit('refresh')
                        }).finally(() => {
                            this.loading = false;
                        })
                    })
                }else {
                    this.$prompt('确认审核不通过吗').then((val) => {
                        this.loading = true;
                        let data = {id:this.scope.row.id,status:3,reason:val.value}
                        api.post('/member/identity_do', data).then((data) => {
                            this.$emit('refresh')
                        }).finally(() => {
                            this.loading = false;
                        })
                    })
                }
            }
        },
        created(){
        },
        components: {
        }
    }
</script>

<style scoped>

</style>