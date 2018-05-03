<template>
    <page title="录入商户" :breadcrumbs="{'商户池': '/merchant/pool'}">
        <el-form size="small" label-width="120px">
            <merchant-pool-form v-loading="isLoading" ref="poolForm"/>
            <!-- 确定按钮 -->
            <el-col>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="doAdd">提交</el-button>
                </el-form-item>
            </el-col>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantPoolForm from '../merchant/merchant-pool-info-form'
    export default {
        name: "merchant-pool-add",
        components: {
            MerchantPoolForm,
        },
        data() {
            return {
                isLoading: false,
            }
        },
        methods: {
            doAdd(){
                let poolForm = this.$refs.poolForm;
                poolForm.validate(() => {
                    let data = poolForm.getData();
                    this.isLoading = true;
                    api.post('/merchant/pool/add', data).then(() => {
                        this.$message.success('保存成功');
                        router.push('/merchant/pool');
                    }).finally(() => {
                        this.isLoading = false;
                    })
                })
            },
            cancel(){
                router.push('/merchant/pool');
            },
        }
    }
</script>

<style scoped>

</style>