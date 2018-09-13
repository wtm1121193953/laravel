<template>
    <page title="添加运营中心" :breadcrumbs="{运营中心管理: '/opers'}" v-loading="loading">
        <el-col :span="12">
            <oper-form
                    ref="addForm"
                    @cancel="cancel"
                    @save="doAdd"/>
        </el-col>
    </page>
</template>

<script>
    import OperForm from './oper-form'
    import api from '../../../assets/js/api'

    export default {
        name: "add",
        data(){
            return {
                loading: false,
                oper: null,
                id: null,
            }
        },
        methods: {
            cancel(){
                router.push('/opers');
            },
            doAdd(data){
                this.loading = true;
                api.post('/oper/add', data).then(() => {
                    this.$refs.addForm.resetForm();
                    router.push('/opers');
                }).finally(() => {
                    this.loading = false;
                })
            }
        },
        created(){
        },
        components: {
            OperForm
        }
    }
</script>

<style scoped>

</style>