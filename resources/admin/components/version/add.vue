<template>
    <page title="添加版本" :breadcrumbs="{APP版本管理: '/versions'}" v-loading="loading">
        <el-col :span="12">
            <version-form
                    ref="addForm"
                    @cancel="cancel"
                    @save="doAdd"/>
        </el-col>
    </page>
</template>

<script>
    import VersionForm from './version-form'
    import api from '../../../assets/js/api'

    export default {
        name: "add",
        data(){
            return {
                loading: false,
                version: null,
                id: null,
            }
        },
        methods: {
            cancel(){
                router.push('/versions');
            },
            doAdd(data){
                this.loading = true;
                api.post('/version/add', data).then(() => {
                    this.$refs.addForm.resetForm();
                    router.push('/versions');
                }).finally(() => {
                    this.loading = false;
                })
            }
        },
        created(){
        },
        components: {
            VersionForm
        }
    }
</script>

<style scoped>

</style>