<template>
    <page title="添加商品" v-loading="isLoading" :breadcrumbs="{商品管理: '/goods'}">
        <el-col :span="16">
            <goods-form
                    ref="addForm"
                    @cancel="cancel"
                    @save="doAdd"/>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import GoodsForm from './goods-form'
    export default {
        name: "add",
        data() {
            return {
                isLoading: false,
            }
        },
        methods: {
            cancel(){
                router.push('/goods');
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/goods/add', data).then(() => {
                    this.$message.success('添加成功');
                    this.$refs.addForm.resetForm();
                    router.push('/goods');
                }).finally(() => {
                    this.isLoading = false;
                })
            },
        },
        components: {
            GoodsForm
        }
    }
</script>

<style scoped>

</style>