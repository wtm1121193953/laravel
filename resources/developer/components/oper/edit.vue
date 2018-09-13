<template>
    <page title="修改运营中心" :breadcrumbs="{运营中心管理: '/opers'}" v-loading="loading">
        <el-col :span="12">
            <oper-form
                    v-if="oper"
                    :data="oper"
                    ref="addForm"
                    @cancel="cancel"
                    @save="doEdit"/>
        </el-col>
    </page>
</template>

<script>
    import OperForm from './oper-form'
    import api from '../../../assets/js/api'

    export default {
        name: "edit",
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
            getDetail(){
                this.loading = true;
                api.get('oper/detail', {id: this.id}).then(data => {
                    this.oper = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            doEdit(data){
                this.loading = true;
                api.post('/oper/edit', data).then((data) => {
                    router.push('/opers');
                }).finally(() => {
                    this.loading = false;
                })
            },
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/opers');
                return ;
            }
            this.getDetail();
        },
        components: {
            OperForm
        }
    }
</script>

<style scoped>

</style>