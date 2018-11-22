<template>
    <page title="编辑商品" :breadcrumbs="{商品管理: '/goods'}">
        <el-col :span="16">
            <fast-goods-form
                    v-if="goods"
                    :data="goods"
                    @cancel="cancel"
                    @save="doEdit"/>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import FastGoodsForm from './fast-goods-form'
    export default {
        name: "fast-edit",
        data(){
            return {
                id: null,
                goods: null,
            }
        },
        methods: {
            cancel(){
                router.push('/goods');
            },
            doEdit(data){
                api.post('/goods/fastEdit', data).then((data) => {
                    router.push('/goods');
                }).finally(() => {

                })
            },
            getDetail(){
                api.get('/goods/detail', {id: this.id}).then(data => {
                    this.goods = data;
                });
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/goods');
                return false;
            }
            this.getDetail();
        },
        components: {
            FastGoodsForm
        }
    }
</script>

<style scoped>

</style>