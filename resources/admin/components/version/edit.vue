<template>
    <page title="修改版本" :breadcrumbs="{APP版本管理: '/versions'}" v-loading="loading">
        <el-col :span="12">
            <version-form
                    v-if="version"
                    :data="version"
                    ref="addForm"
                    @cancel="cancel"
                    @save="doEdit"/>
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
            getDetail(){
                this.loading = true;
                api.get('/version/detail', {id: this.id}).then(data => {
                    this.version = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            doEdit(data){
                this.loading = true;
                api.post('/version/edit', data).then((data) => {
                    router.push('/versions');
                }).finally(() => {
                    this.loading = false;
                })
            },
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/versions');
                return false;
            }
            this.getDetail();
        },
        components: {
            VersionForm
        }
    }
</script>

<style scoped>

</style>